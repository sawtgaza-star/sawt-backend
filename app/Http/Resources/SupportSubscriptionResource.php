<?php

namespace App\Http\Resources;

use App\Models\SupportSubscription;
use App\Support\SupportOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SupportSubscription
 */
class SupportSubscriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'status_label' => SupportOptions::subscriptionStatuses()[$this->status] ?? $this->status,
            'interval' => $this->interval,
            'interval_label' => SupportOptions::intervalLabels()[$this->interval] ?? null,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'gateway' => $this->gateway,
            'gateway_subscription_id' => $this->gateway_subscription_id,
            'subscriber' => [
                'name' => $this->subscriber_name,
                'email' => $this->subscriber_email,
            ],
            'started_at' => $this->started_at?->toIso8601String(),
            'next_billing_at' => $this->next_billing_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cycles_completed' => $this->cycles_completed,
            'total_paid' => (float) $this->total_paid,
            'is_cancellable' => $this->isCancellable(),
        ];
    }
}
