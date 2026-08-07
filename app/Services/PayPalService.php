<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

    /* =========================================================================
     | PayPal Billing — المنتجات والخطط والاشتراكات الدورية (شهري / سنوي)
     ========================================================================= */

    /**
     * Create (or reuse) the catalog product every subscription plan hangs off.
     */
    public function createProduct(string $name, string $description = ''): array
    {
        $response = Http::withToken($this->accessToken())
            ->withHeaders(['PayPal-Request-Id' => 'product-'.md5($name)])
            ->post("{$this->baseUrl()}/v1/catalogs/products", [
                'name' => mb_substr($name, 0, 127),
                'description' => mb_substr($description, 0, 256) ?: null,
                'type' => 'SERVICE',
                'category' => 'CHARITY',
            ]);

        if ($response->failed()) {
            Log::error('PayPal create product failed', ['body' => $response->json()]);
            throw new RuntimeException('Unable to create PayPal product.');
        }

        return $response->json();
    }

    /**
     * Create a billing plan (MONTH / YEAR) for recurring support.
     *
     * @param  string  $interval  monthly|yearly
     */
    public function createPlan(string $productId, string $name, float $amount, string $currency, string $interval): array
    {
        $response = Http::withToken($this->accessToken())
            ->post("{$this->baseUrl()}/v1/billing/plans", [
                'product_id' => $productId,
                'name' => mb_substr($name, 0, 127),
                'status' => 'ACTIVE',
                'billing_cycles' => [[
                    'frequency' => [
                        'interval_unit' => $interval === 'yearly' ? 'YEAR' : 'MONTH',
                        'interval_count' => 1,
                    ],
                    'tenure_type' => 'REGULAR',
                    'sequence' => 1,
                    'total_cycles' => 0, // 0 = يستمر حتى الإلغاء
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => number_format($amount, 2, '.', ''),
                            'currency_code' => strtoupper($currency),
                        ],
                    ],
                ]],
                'payment_preferences' => [
                    'auto_bill_outstanding' => true,
                    'setup_fee_failure_action' => 'CONTINUE',
                    'payment_failure_threshold' => 3,
                ],
            ]);

        if ($response->failed()) {
            Log::error('PayPal create plan failed', ['body' => $response->json()]);
            throw new RuntimeException('Unable to create PayPal billing plan.');
        }

        return $response->json();
    }

    /**
     * Start a subscription against a plan. Returns the decoded subscription
     * (contains "id" and the approve link the donor must visit).
     *
     * @param  array{name?: string, email?: string}  $subscriber
     */
    public function createSubscription(string $planId, array $subscriber = [], ?string $customId = null, ?string $returnUrl = null, ?string $cancelUrl = null): array
    {
        $payload = ['plan_id' => $planId];

        if ($customId !== null) {
            $payload['custom_id'] = $customId;
        }

        if (filled($subscriber['email'] ?? null) || filled($subscriber['name'] ?? null)) {
            $payload['subscriber'] = array_filter([
                'email_address' => $subscriber['email'] ?? null,
                'name' => filled($subscriber['name'] ?? null) ? [
                    'given_name' => Str::before(trim($subscriber['name']), ' ') ?: $subscriber['name'],
                    'surname' => Str::after(trim($subscriber['name']), ' ') ?: '-',
                ] : null,
            ]);
        }

        if ($returnUrl || $cancelUrl) {
            $payload['application_context'] = array_filter([
                'brand_name' => Setting::get('site_name', 'Sawt'),
                'user_action' => 'SUBSCRIBE_NOW',
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ]);
        }

        $response = Http::withToken($this->accessToken())
            ->post("{$this->baseUrl()}/v1/billing/subscriptions", $payload);

        if ($response->failed()) {
            Log::error('PayPal create subscription failed', ['body' => $response->json()]);
            throw new RuntimeException('Unable to create PayPal subscription.');
        }

        return $response->json();
    }

    public function getSubscription(string $subscriptionId): array
    {
        $response = Http::withToken($this->accessToken())
            ->get("{$this->baseUrl()}/v1/billing/subscriptions/{$subscriptionId}");

        if ($response->failed()) {
            Log::error('PayPal get subscription failed', ['id' => $subscriptionId, 'body' => $response->json()]);
            throw new RuntimeException('Unable to fetch PayPal subscription.');
        }

        return $response->json();
    }

    public function cancelSubscription(string $subscriptionId, string $reason = 'Cancelled by donor'): bool
    {
        $response = Http::withToken($this->accessToken())
            ->post("{$this->baseUrl()}/v1/billing/subscriptions/{$subscriptionId}/cancel", [
                'reason' => mb_substr($reason, 0, 127),
            ]);

        if ($response->failed()) {
            Log::error('PayPal cancel subscription failed', ['id' => $subscriptionId, 'body' => $response->json()]);

            return false;
        }

        return true;
    }

    public function suspendSubscription(string $subscriptionId, string $reason = 'Paused by donor'): bool
    {
        return Http::withToken($this->accessToken())
            ->post("{$this->baseUrl()}/v1/billing/subscriptions/{$subscriptionId}/suspend", [
                'reason' => mb_substr($reason, 0, 127),
            ])->successful();
    }

    public function activateSubscription(string $subscriptionId, string $reason = 'Resumed by donor'): bool
    {
        return Http::withToken($this->accessToken())
            ->post("{$this->baseUrl()}/v1/billing/subscriptions/{$subscriptionId}/activate", [
                'reason' => mb_substr($reason, 0, 127),
            ])->successful();
    }

    /**
     * Pull the donor-facing approval link out of a create-subscription response.
     */
    public function approvalLink(array $subscription): ?string
    {
        foreach ($subscription['links'] ?? [] as $link) {
            if (($link['rel'] ?? null) === 'approve') {
                return $link['href'] ?? null;
            }
        }

        return null;
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
