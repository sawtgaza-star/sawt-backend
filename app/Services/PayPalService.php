<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayPalService
{
    /** Base URL depends on sandbox/live mode. */
    public function baseUrl(): string
    {
        return $this->mode() === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function mode(): string
    {
        return Setting::get('paypal_mode', config('services.paypal.mode', 'sandbox')) === 'live'
            ? 'live'
            : 'sandbox';
    }

    public function clientId(): ?string
    {
        return Setting::get('paypal_client_id', config('services.paypal.client_id')) ?: null;
    }

    protected function secret(): ?string
    {
        return Setting::get('paypal_secret', config('services.paypal.secret')) ?: null;
    }

    public function isConfigured(): bool
    {
        return $this->clientId() && $this->secret();
    }

    /**
     * OAuth2 access token (client_credentials). Cached per mode until ~just before expiry.
     */
    public function accessToken(): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('PayPal credentials are not configured.');
        }

        return Cache::remember("paypal.token.{$this->mode()}", 500, function () {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId(), $this->secret())
                ->post("{$this->baseUrl()}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->failed()) {
                Log::error('PayPal token error', ['body' => $response->json()]);
                throw new RuntimeException('Unable to obtain PayPal access token.');
            }

            return $response->json('access_token');
        });
    }

    /**
     * Create a PayPal order. Returns the decoded order (contains "id").
     *
     * @param  string  $reference  internal reference (e.g. "donation:12" or "course:5")
     */
    public function createOrder(float $amount, string $currency, string $reference, string $description = '', ?string $customId = null): array
    {
        $unit = [
            'reference_id' => $reference,
            'description' => mb_substr($description, 0, 127),
            'amount' => [
                'currency_code' => strtoupper($currency),
                'value' => number_format($amount, 2, '.', ''),
            ],
        ];

        if ($customId !== null) {
            $unit['custom_id'] = $customId; // maps webhook events back to our Payment
        }

        $response = Http::withToken($this->accessToken())
            ->post("{$this->baseUrl()}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [$unit],
            ]);

        if ($response->failed()) {
            Log::error('PayPal create order failed', ['body' => $response->json()]);
            throw new RuntimeException('Unable to create PayPal order.');
        }

        return $response->json();
    }

    /**
     * Capture an approved order. Returns the decoded capture response.
     */
    public function captureOrder(string $orderId): array
    {
        $response = Http::withToken($this->accessToken())
            ->post("{$this->baseUrl()}/v2/checkout/orders/{$orderId}/capture");

        if ($response->failed()) {
            Log::error('PayPal capture failed', ['order' => $orderId, 'body' => $response->json()]);
            throw new RuntimeException('Unable to capture PayPal order.');
        }

        return $response->json();
    }

    /**
     * Refund a captured payment.
     */
    public function refund(string $captureId, ?float $amount = null, string $currency = 'USD'): array
    {
        $payload = [];
        if ($amount !== null) {
            $payload['amount'] = [
                'value' => number_format($amount, 2, '.', ''),
                'currency_code' => strtoupper($currency),
            ];
        }

        $response = Http::withToken($this->accessToken())
            ->post("{$this->baseUrl()}/v2/payments/captures/{$captureId}/refund", $payload);

        if ($response->failed()) {
            Log::error('PayPal refund failed', ['capture' => $captureId, 'body' => $response->json()]);
            throw new RuntimeException('Unable to refund PayPal payment.');
        }

        return $response->json();
    }

    /**
     * Verify a webhook signature with PayPal so we only trust genuine events.
     */
    public function verifyWebhook(array $headers, string $rawBody): bool
    {
        $webhookId = Setting::get('paypal_webhook_id', config('services.paypal.webhook_id'));

        if (empty($webhookId)) {
            return false;
        }

        $header = fn (string $key) => $headers[strtolower($key)][0] ?? ($headers[$key][0] ?? null);

        $response = Http::withToken($this->accessToken())
            ->post("{$this->baseUrl()}/v1/notifications/verify-webhook-signature", [
                'auth_algo' => $header('paypal-auth-algo'),
                'cert_url' => $header('paypal-cert-url'),
                'transmission_id' => $header('paypal-transmission-id'),
                'transmission_sig' => $header('paypal-transmission-sig'),
                'transmission_time' => $header('paypal-transmission-time'),
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($rawBody, true),
            ]);

        return $response->json('verification_status') === 'SUCCESS';
    }
}
