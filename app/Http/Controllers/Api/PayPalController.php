<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CheckoutService;
use App\Services\PayPalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PayPalController extends Controller
{
    public function __construct(
        protected PayPalService $paypal,
        protected CheckoutService $checkout,
    ) {}

    /** Expose the client id + mode so the frontend can load the PayPal JS SDK. */
    public function config(): JsonResponse
    {
        return response()->json([
            'configured' => $this->paypal->isConfigured(),
            'client_id' => $this->paypal->clientId(),
            'mode' => $this->paypal->mode(),
            'currency' => 'USD',
        ]);
    }

    /** Create an order for a paid course (auth required). */
    public function createCourseOrder(Request $request, Course $course): JsonResponse
    {
        try {
            $result = $this->checkout->startCourseOrder($course, $request->user());

            return response()->json(['id' => $result['order_id']]);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /** Create an order for a donation (guest allowed). */
    public function createDonationOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'size:3'],
            'donor_name' => ['nullable', 'string', 'max:255'],
            'donor_email' => ['nullable', 'email', 'max:255'],
        ]);

        try {
            $result = $this->checkout->startDonationOrder($data, $request->user());

            return response()->json(['id' => $result['order_id']]);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /** Capture an approved order (called from the Smart Button onApprove). */
    public function capture(string $orderId): JsonResponse
    {
        try {
            $payment = $this->checkout->capture($orderId);

            return response()->json([
                'status' => $payment->status,
                'payment' => $payment->uuid,
            ]);
        } catch (Throwable $e) {
            Log::error('PayPal capture endpoint failed', ['order' => $orderId, 'error' => $e->getMessage()]);

            return response()->json(['error' => 'Capture failed.'], 422);
        }
    }

    /** PayPal webhook — verified safety net that confirms/fulfills payments. */
    public function webhook(Request $request): JsonResponse
    {
        if (! $this->paypal->verifyWebhook($request->headers->all(), $request->getContent())) {
            return response()->json(['error' => 'invalid signature'], 400);
        }

        $event = $request->input('event_type');
        $resource = $request->input('resource', []);

        if ($event === 'PAYMENT.CAPTURE.COMPLETED') {
            $payment = $this->checkout->findByCustomId($resource['custom_id'] ?? null);

            if ($payment && ! $payment->isCompleted()) {
                $this->checkout->completeFromWebhook($payment, $resource);
            }
        }

        return response()->json(['received' => true]);
    }
}
