<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutService
{
    public function __construct(protected PayPalService $paypal) {}

    /**
     * Begin a course purchase: create (or reuse) a pending enrollment + payment and a PayPal order.
     *
     * @return array{order_id: string, payment: Payment}
     */
    public function startCourseOrder(Course $course, User $user): array
    {
        if ($course->isFree()) {
            throw new RuntimeException('Course is free — no payment required.');
        }

        if ($course->isPurchasedBy($user)) {
            throw new RuntimeException('Course already purchased.');
        }

        $enrollment = CourseEnrollment::firstOrCreate(
            ['course_id' => $course->id, 'user_id' => $user->id],
            ['status' => 'pending', 'price_paid' => $course->price, 'currency' => $course->currency],
        );

        return $this->startOrder(
            $enrollment,
            $user->id,
            (float) $course->price,
            $course->currency,
            "course:{$course->id}",
            'Course: '.$course->getTranslation('title', 'en'),
        );
    }

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
     * Shared: create the pending Payment first, then a PayPal order tagged with the
     * payment UUID as custom_id so webhooks map straight back to it.
     *
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

    /**
     * Capture an approved order and fulfill it. Idempotent — safe to call from both
     * the client onApprove and the webhook.
     */
    public function capture(string $orderId): Payment
    {
        $payment = Payment::where('gateway_order_id', $orderId)->firstOrFail();

        return $this->completeFromCapture($payment, fn () => $this->paypal->captureOrder($orderId));
    }

    /**
     * Resolve a Payment from a webhook custom_id ("payment:{uuid}").
     */
    public function findByCustomId(?string $customId): ?Payment
    {
        if (! $customId || ! str_starts_with($customId, 'payment:')) {
            return null;
        }

        return Payment::where('uuid', substr($customId, strlen('payment:')))->first();
    }

    /**
     * Mark a payment completed from an already-captured webhook resource (no extra API call).
     */
    public function completeFromWebhook(Payment $payment, array $captureResource): Payment
    {
        return $this->completeFromCapture($payment, fn () => [
            'status' => 'COMPLETED',
            'purchase_units' => [['payments' => ['captures' => [$captureResource]]]],
        ]);
    }

    /**
     * @param  callable(): array  $resolver  returns a PayPal order/capture payload
     */
    protected function completeFromCapture(Payment $payment, callable $resolver): Payment
    {
        if ($payment->isCompleted()) {
            return $payment; // already handled
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

    /** Grant what was paid for. */
    protected function fulfill(Payment $payment): void
    {
        $payable = $payment->payable;

        if ($payable instanceof CourseEnrollment && $payable->status !== 'active') {
            $payable->update(['status' => 'active', 'enrolled_at' => now()]);
            $payable->course()->increment('students_count');
        }

        if ($payable instanceof Donation && $payable->status !== 'succeeded') {
            $payable->update(['status' => 'succeeded']);
            if ($payable->campaign_id) {
                $payable->campaign()->increment('current_amount', $payable->amount);
            }
        }
    }
}
