<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Payment;
use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(protected PayPalService $paypal) {}

    /**
     * Begin a donation: create a pending donation + payment and a PayPal order.
     *
     * @param  array{campaign_id?: int|null, amount: float, currency?: string, donor_name?: string, donor_email?: string}  $data
     * @return array{order_id: string, payment: Payment}
     */
    public function startDonationOrder(array $data, ?User $user = null): array
    {
        $amount = (float) $data['amount'];
        $currency = $data['currency'] ?? 'USD';

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

        return $this->startOrder($donation, $user?->id, $amount, $currency, "donation:{$donation->id}", 'Donation');
    }

    /**
     * إنشاء أمر PayPal لأي كيان قابل للدفع (تبرع، طلب دعم…) — نسخة عامة من startOrder.
     *
     * @return array{order_id: string, payment: Payment}
     */
    public function startOrderFor(object $payable, ?int $userId, float $amount, string $currency, string $reference, string $description): array
    {
        return $this->startOrder($payable, $userId, $amount, $currency, $reference, $description);
    }

    /**
     * @return array{order_id: string, payment: Payment}
     */
    protected function startOrder(object $payable, ?int $userId, float $amount, string $currency, string $reference, string $description): array
    {
        return DB::transaction(function () use ($payable, $userId, $amount, $currency, $reference, $description) {
            $payment = $payable->payment()->updateOrCreate([], [
                'user_id' => $userId,
                'gateway' => 'paypal',
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
            ]);

            $order = $this->paypal->createOrder($amount, $currency, $reference, $description, "payment:{$payment->uuid}");

            $payment->update(['gateway_order_id' => $order['id']]);

            return ['order_id' => $order['id'], 'payment' => $payment->refresh()];
        });
    }

    public function capture(string $orderId): Payment
    {
        $payment = Payment::where('gateway_order_id', $orderId)->firstOrFail();

        return $this->completeFromCapture($payment, fn () => $this->paypal->captureOrder($orderId));
    }

    public function findByCustomId(?string $customId): ?Payment
    {
        if (! $customId || ! str_starts_with($customId, 'payment:')) {
            return null;
        }

        return Payment::where('uuid', substr($customId, strlen('payment:')))->first();
    }

    public function completeFromWebhook(Payment $payment, array $captureResource): Payment
    {
        return $this->completeFromCapture($payment, fn () => [
            'status' => 'COMPLETED',
            'purchase_units' => [['payments' => ['captures' => [$captureResource]]]],
        ]);
    }

    /**
     * @param  callable(): array  $resolver
     */
    protected function completeFromCapture(Payment $payment, callable $resolver): Payment
    {
        if ($payment->isCompleted()) {
            return $payment;
        }

        $result = $resolver();
        $capture = data_get($result, 'purchase_units.0.payments.captures.0');
        $status = data_get($result, 'status', data_get($capture, 'status'));

        return DB::transaction(function () use ($payment, $result, $capture, $status) {
            $payment->fill([
                'status' => in_array($status, ['COMPLETED'], true) ? 'completed' : 'failed',
                'gateway_capture_id' => data_get($capture, 'id') ?: $payment->gateway_capture_id,
                'payer_email' => data_get($result, 'payer.email_address') ?: $payment->payer_email,
                'payer_name' => trim(data_get($result, 'payer.name.given_name', '').' '.data_get($result, 'payer.name.surname', '')) ?: $payment->payer_name,
                'meta' => $result,
                'paid_at' => now(),
            ])->save();

            if ($payment->isCompleted()) {
                $this->fulfill($payment);
            }

            return $payment;
        });
    }

    protected function fulfill(Payment $payment): void
    {
        $payable = $payment->payable;

        if ($payable instanceof Donation && $payable->status !== 'succeeded') {
            $payable->update(['status' => 'succeeded']);
            if ($payable->campaign_id) {
                $payable->campaign()->increment('current_amount', $payable->amount);
            }

            $this->approveLinkedSupportRequest($payable);
        }
    }

    /**
     * التبرع الناتج عن ويزارد الدعم بمسار الدفع الإلكتروني يُعتمد آلياً —
     * لا يحتاج مراجعة يدوية لأن PayPal أكّد التحصيل.
     */
    protected function approveLinkedSupportRequest(Donation $donation): void
    {
        SupportRequest::where('donation_id', $donation->id)
            ->whereIn('status', ['draft', 'pending', 'under_review'])
            ->get()
            ->each(function (SupportRequest $request): void {
                $request->update([
                    'status' => 'approved',
                    'reviewed_at' => now(),
                    'submitted_at' => $request->submitted_at ?? now(),
                    'current_step' => max($request->current_step, 4),
                ]);
            });
    }
}
