<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Payment;
use App\Models\SupportMethod;
use App\Models\SupportRequest;
use App\Models\SupportSubscription;
use App\Models\User;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\SupportRepositoryInterface;
use App\Support\FrontendUrl;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * «ادعم صوت» السريع: المتبرع يدخل المبلغ والدورية فقط ويتحوّل مباشرة لـ PayPal.
 *
 *   one_time        → أمر PayPal (Orders v2) + طلب دعم يُعتمد آلياً بعد التحصيل
 *   monthly/yearly  → اشتراك PayPal Billing
 *
 * التدفق: POST /support/checkout ← redirect إلى approval_url ← PayPal يرجع للفرونت
 * ← POST /support/checkout/confirm بنفس باراميترات الرجوع (token أو subscription_id).
 */
class SupportCheckoutService
{
    public function __construct(
        protected PayPalService $paypal,
        protected CheckoutService $checkout,
        protected SupportSubscriptionService $subscriptions,
        protected SupportRepositoryInterface $support,
        protected SettingRepositoryInterface $settings,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{type: string, approval_url: string, reference: string}
     *
     * @throws RuntimeException|ModelNotFoundException
     */
    public function start(array $data, ?User $user = null): array
    {
        if (! $this->paypal->isConfigured()) {
            throw new RuntimeException('الدفع عبر PayPal غير مُفعّل حالياً.');
        }

        $plan = filled($data['plan_uuid'] ?? null)
            ? $this->support->findActivePlanByUuid((string) $data['plan_uuid'])
            : null;

        $interval = $plan?->interval ?? ($data['interval'] ?? 'one_time');

        if ($interval !== 'one_time') {
            $result = $this->subscriptions->create([
                ...$data,
                'interval' => $interval,
                'subscriber_name' => $data['donor_name'] ?? null,
                'subscriber_email' => $data['donor_email'] ?? null,
            ], $user);

            if (! $result['approval_url']) {
                throw new RuntimeException('تعذّر الحصول على رابط الموافقة من PayPal.');
            }

            return [
                'type' => 'subscription',
                'approval_url' => $result['approval_url'],
                'reference' => $result['subscription']->uuid,
            ];
        }

        return $this->startOneTime($data, $plan?->amount, $plan?->currency, $user);
    }

    /**
     * تأكيد الدفع بعد رجوع المتبرع من PayPal.
     *
     * @param  array{token?: string|null, subscription_id?: string|null}  $data
     * @return array<string, mixed>
     */
    public function confirm(array $data): array
    {
        if (filled($data['subscription_id'] ?? null)) {
            $subscription = $this->subscriptions->findByGatewayId((string) $data['subscription_id']);

            if (! $subscription) {
                throw (new ModelNotFoundException)->setModel(SupportSubscription::class);
            }

            $subscription = $this->subscriptions->activate($subscription);

            return [
                'type' => 'subscription',
                'status' => $subscription->status,
                'reference' => $subscription->uuid,
                'interval' => $subscription->interval,
                'amount' => (float) $subscription->amount,
                'currency' => $subscription->currency,
                'donor_name' => $subscription->subscriber_name,
                'donor_email' => $subscription->subscriber_email,
                'paypal_id' => $subscription->gateway_subscription_id,
                'paid_at' => $subscription->started_at?->toIso8601String(),
                'next_billing_at' => $subscription->next_billing_at?->toIso8601String(),
            ];
        }

        // Orders v2 redirect: return_url?token=<order id>&PayerID=…
        $payment = $this->checkout->capture((string) $data['token']);

        return [
            'type' => 'one_time',
            'status' => $payment->status,
            'reference' => $this->supportRequestUuidFor($payment),
            'interval' => 'one_time',
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'donor_name' => $payment->payer_name ?: $payment->payable?->donor_name,
            'donor_email' => $payment->payer_email ?: $payment->payable?->donor_email,
            'paypal_id' => $payment->gateway_capture_id ?: $payment->gateway_order_id,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'next_billing_at' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{type: string, approval_url: string, reference: string}
     */
    protected function startOneTime(array $data, mixed $planAmount, ?string $planCurrency, ?User $user): array
    {
        $amount = (float) ($planAmount ?? ($data['amount'] ?? 0));
        $currency = strtoupper((string) ($planCurrency ?? $data['currency'] ?? $this->defaultCurrency()));

        $min = (float) ($this->settings->get('support_min_amount', $this->settings->get('min_donation_amount', 5)) ?: 5);
        if ($amount < $min) {
            throw new RuntimeException("الحد الأدنى للدعم هو {$min}.");
        }

        return DB::transaction(function () use ($data, $amount, $currency, $user) {
            $donation = Donation::create([
                'campaign_id' => $data['campaign_id'] ?? null,
                'user_id' => $user?->id,
                'donor_name' => $data['donor_name'] ?? $user?->name,
                'donor_email' => $data['donor_email'] ?? $user?->email,
                'amount' => $amount,
                'currency' => $currency,
                'payment_method' => 'paypal',
                'status' => 'pending',
            ]);

            // يبقى draft حتى يؤكد PayPal التحصيل، ثم يُعتمد آلياً (CheckoutService::fulfill)
            $request = SupportRequest::create([
                'user_id' => $user?->id,
                'support_method_id' => $this->paypalMethod()?->id,
                'campaign_id' => $data['campaign_id'] ?? null,
                'donation_id' => $donation->id,
                'category' => 'electronic',
                'interval' => 'one_time',
                'amount' => $amount,
                'currency' => $currency,
                'donor_name' => $data['donor_name'] ?? $user?->name,
                'donor_email' => $data['donor_email'] ?? $user?->email,
                'status' => 'draft',
                'current_step' => 1,
            ]);

            $order = $this->checkout->startOrderFor(
                $donation,
                $user?->id,
                $amount,
                $currency,
                "support:{$request->uuid}",
                'Support Sawt',
                FrontendUrl::sameHostOr($data['return_url'] ?? null, FrontendUrl::supportReturn()),
                FrontendUrl::sameHostOr($data['cancel_url'] ?? null, FrontendUrl::supportCancel()),
            );

            if (! $order['approval_url']) {
                throw new RuntimeException('تعذّر الحصول على رابط الدفع من PayPal.');
            }

            return [
                'type' => 'one_time',
                'approval_url' => $order['approval_url'],
                'reference' => $request->uuid,
            ];
        });
    }

    protected function paypalMethod(): ?SupportMethod
    {
        return SupportMethod::query()
            ->where('category', 'electronic')
            ->where('provider', 'paypal')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();
    }

    protected function supportRequestUuidFor(Payment $payment): ?string
    {
        $payable = $payment->payable;

        return $payable instanceof Donation
            ? SupportRequest::where('donation_id', $payable->id)->value('uuid')
            : null;
    }

    protected function defaultCurrency(): string
    {
        return (string) ($this->settings->get('support_default_currency', 'USD') ?: 'USD');
    }
}
