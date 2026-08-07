<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Setting;
use App\Models\SupportPlan;
use App\Models\SupportSubscription;
use App\Models\User;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\SupportRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * الاشتراك الدوري عبر PayPal Billing.
 *
 * التدفق: إنشاء اشتراك ← المتبرع يوافق على رابط PayPal ← نستدعي activate()
 * لتأكيد الحالة، والـ webhook يبقى شبكة أمان تحدّث الحالة وتسجّل كل دورة تحصيل.
 */
class SupportSubscriptionService
{
    public function __construct(
        protected PayPalService $paypal,
        protected SupportRepositoryInterface $support,
        protected SettingRepositoryInterface $settings,
    ) {}

    /**
     * إنشاء اشتراك من باقة جاهزة أو بمبلغ مخصص.
     *
     * @param  array<string, mixed>  $data
     * @return array{subscription: SupportSubscription, approval_url: ?string}
     */
    public function create(array $data, ?User $user = null): array
    {
        $plan = filled($data['plan_uuid'] ?? null)
            ? $this->support->findActivePlanByUuid((string) $data['plan_uuid'])
            : null;

        if (filled($data['plan_uuid'] ?? null) && ! $plan) {
            throw (new ModelNotFoundException)->setModel(SupportPlan::class, [$data['plan_uuid']]);
        }

        $interval = $plan?->interval ?? ($data['interval'] ?? 'monthly');

        if (! in_array($interval, ['monthly', 'yearly'], true)) {
            throw new RuntimeException('الاشتراك الدوري يقبل «شهري» أو «سنوي» فقط.');
        }

        $amount = (float) ($plan?->amount ?? ($data['amount'] ?? 0));
        $currency = strtoupper((string) ($plan?->currency ?? $data['currency'] ?? $this->defaultCurrency()));

        $min = (float) ($this->settings->get('support_min_amount', $this->settings->get('min_donation_amount', 5)) ?: 5);
        if ($amount < $min) {
            throw new RuntimeException("الحد الأدنى للاشتراك هو {$min}.");
        }

        // خطة PayPal: نستخدم خطة الباقة المحفوظة، وإلا نصنع واحدة للمبلغ المطلوب ونخزّنها للاستخدام لاحقاً
        $paypalPlanId = $plan?->paypal_plan_id ?: $this->resolvePlanId($amount, $currency, $interval, $plan);

        $subscription = SupportSubscription::create([
            'user_id' => $user?->id,
            'support_plan_id' => $plan?->id,
            'gateway' => 'paypal',
            'gateway_plan_id' => $paypalPlanId,
            'interval' => $interval,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'approval_pending',
            'subscriber_name' => $data['subscriber_name'] ?? $user?->name,
            'subscriber_email' => $data['subscriber_email'] ?? $user?->email,
        ]);

        $remote = $this->paypal->createSubscription(
            $paypalPlanId,
            [
                'name' => $subscription->subscriber_name,
                'email' => $subscription->subscriber_email,
            ],
            "subscription:{$subscription->uuid}",
            $data['return_url'] ?? null,
            $data['cancel_url'] ?? null,
        );

        $subscription->update([
            'gateway_subscription_id' => $remote['id'] ?? null,
            'meta' => $remote,
        ]);

        return [
            'subscription' => $subscription->fresh(),
            'approval_url' => $this->paypal->approvalLink($remote),
        ];
    }

    /**
     * تأكيد الاشتراك بعد موافقة المتبرع (يُستدعى من onApprove بالفرونت).
     */
    public function activate(SupportSubscription $subscription): SupportSubscription
    {
        if (! $subscription->gateway_subscription_id) {
            throw new RuntimeException('لا يوجد اشتراك مرتبط بـ PayPal.');
        }

        $remote = $this->paypal->getSubscription($subscription->gateway_subscription_id);

        return $this->syncFromRemote($subscription, $remote);
    }

    public function cancel(SupportSubscription $subscription, string $reason = 'Cancelled by donor'): SupportSubscription
    {
        if (! $subscription->isCancellable()) {
            throw new RuntimeException('لا يمكن إلغاء هذا الاشتراك بحالته الحالية.');
        }

        if ($subscription->gateway_subscription_id) {
            $this->paypal->cancelSubscription($subscription->gateway_subscription_id, $reason);
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return $subscription->fresh();
    }

    /**
     * مزامنة الحالة والمبالغ من رد PayPal.
     *
     * @param  array<string, mixed>  $remote
     */
    public function syncFromRemote(SupportSubscription $subscription, array $remote): SupportSubscription
    {
        $status = strtoupper((string) ($remote['status'] ?? ''));

        $map = [
            'APPROVAL_PENDING' => 'approval_pending',
            'APPROVED' => 'approval_pending',
            'ACTIVE' => 'active',
            'SUSPENDED' => 'suspended',
            'CANCELLED' => 'cancelled',
            'EXPIRED' => 'expired',
        ];

        $subscription->fill([
            'status' => $map[$status] ?? $subscription->status,
            'gateway_subscription_id' => $remote['id'] ?? $subscription->gateway_subscription_id,
            'gateway_plan_id' => $remote['plan_id'] ?? $subscription->gateway_plan_id,
            'subscriber_email' => data_get($remote, 'subscriber.email_address') ?: $subscription->subscriber_email,
            'subscriber_name' => trim(
                data_get($remote, 'subscriber.name.given_name', '').' '.data_get($remote, 'subscriber.name.surname', '')
            ) ?: $subscription->subscriber_name,
            'started_at' => data_get($remote, 'start_time') ? Carbon::parse($remote['start_time']) : $subscription->started_at,
            'next_billing_at' => data_get($remote, 'billing_info.next_billing_time')
                ? Carbon::parse($remote['billing_info']['next_billing_time'])
                : $subscription->next_billing_at,
            'cycles_completed' => (int) (data_get($remote, 'billing_info.cycle_executions.0.cycles_completed') ?? $subscription->cycles_completed),
            'meta' => $remote,
        ]);

        if ($subscription->status === 'cancelled' && ! $subscription->cancelled_at) {
            $subscription->cancelled_at = now();
        }

        $subscription->save();

        return $subscription->fresh();
    }

    /**
     * تسجيل دورة تحصيل ناجحة كـ Payment مرتبط بالاشتراك (PAYMENT.SALE.COMPLETED).
     *
     * @param  array<string, mixed>  $sale
     */
    public function recordCycle(SupportSubscription $subscription, array $sale): Payment
    {
        $saleId = (string) ($sale['id'] ?? '');
        $amount = (float) (data_get($sale, 'amount.total') ?? data_get($sale, 'amount.value') ?? $subscription->amount);
        $currency = strtoupper((string) (data_get($sale, 'amount.currency') ?? data_get($sale, 'amount.currency_code') ?? $subscription->currency));

        return DB::transaction(function () use ($subscription, $sale, $saleId, $amount, $currency) {
            $existing = $saleId ? Payment::where('gateway_capture_id', $saleId)->first() : null;

            if ($existing) {
                return $existing;
            }

            $payment = $subscription->payments()->create([
                'user_id' => $subscription->user_id,
                'gateway' => 'paypal',
                'gateway_order_id' => $subscription->gateway_subscription_id,
                'gateway_capture_id' => $saleId ?: null,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'completed',
                'payer_email' => $subscription->subscriber_email,
                'payer_name' => $subscription->subscriber_name,
                'meta' => $sale,
                'paid_at' => now(),
            ]);

            $subscription->increment('total_paid', $amount);
            $subscription->increment('cycles_completed');

            if ($subscription->status !== 'active') {
                $subscription->update(['status' => 'active']);
            }

            return $payment;
        });
    }

    public function findByCustomId(?string $customId): ?SupportSubscription
    {
        if (! $customId || ! str_starts_with($customId, 'subscription:')) {
            return null;
        }

        return SupportSubscription::where('uuid', substr($customId, strlen('subscription:')))->first();
    }

    public function findByGatewayId(?string $gatewayId): ?SupportSubscription
    {
        return $gatewayId
            ? SupportSubscription::where('gateway_subscription_id', $gatewayId)->first()
            : null;
    }

    /**
     * خطة PayPal للمبلغ/الدورية المطلوبة — تُنشأ مرة وتُخزَّن بالإعدادات أو بالباقة.
     */
    protected function resolvePlanId(float $amount, string $currency, string $interval, ?SupportPlan $plan = null): string
    {
        $cacheKey = 'paypal_plan_'.$interval.'_'.strtolower($currency).'_'.str_replace('.', '_', number_format($amount, 2, '.', ''));

        if ($stored = Setting::get($cacheKey)) {
            return (string) $stored;
        }

        $productId = $this->resolveProductId();

        $siteName = (string) (Setting::get('site_name', 'Sawt') ?: 'Sawt');
        $label = $interval === 'yearly' ? 'Yearly' : 'Monthly';

        $created = $this->paypal->createPlan(
            $productId,
            "{$siteName} — {$label} Support {$currency} ".number_format($amount, 2, '.', ''),
            $amount,
            $currency,
            $interval,
        );

        $planId = (string) ($created['id'] ?? '');

        if ($planId === '') {
            throw new RuntimeException('تعذّر إنشاء خطة PayPal.');
        }

        Setting::set($cacheKey, $planId, group: 'paypal', type: 'string');

        $plan?->update(['paypal_plan_id' => $planId]);

        return $planId;
    }

    protected function resolveProductId(): string
    {
        if ($stored = Setting::get('paypal_product_id')) {
            return (string) $stored;
        }

        $siteName = (string) (Setting::get('site_name', 'Sawt') ?: 'Sawt');
        $product = $this->paypal->createProduct("{$siteName} Support", 'Recurring support for the platform');
        $productId = (string) ($product['id'] ?? '');

        if ($productId === '') {
            throw new RuntimeException('تعذّر إنشاء منتج PayPal.');
        }

        Setting::set('paypal_product_id', $productId, group: 'paypal', type: 'string');

        return $productId;
    }

    protected function defaultCurrency(): string
    {
        return (string) ($this->settings->get('support_default_currency', 'USD') ?: 'USD');
    }
}
