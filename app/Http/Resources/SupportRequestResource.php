<?php

namespace App\Http\Resources;

use App\Models\SupportRequest;
use App\Support\SupportOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SupportRequest
 */
class SupportRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'status_label' => SupportOptions::requestStatuses()[$this->status] ?? $this->status,
            'category' => $this->category,
            'interval' => $this->interval,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,

            'wizard' => [
                'current_step' => $this->current_step,
                'total_steps' => count(SupportOptions::STEPS),
                'current_step_key' => $this->stepName(),
                'next_step_key' => $this->nextStep(),
                'is_complete' => $this->isSubmitted(),
                'requires_proof' => $this->needsProof(),
                'progress_percent' => $this->isSubmitted()
                    ? 100
                    : (int) round(min($this->current_step - 1, count(SupportOptions::STEPS)) / count(SupportOptions::STEPS) * 100),
            ],

            'method' => $this->whenLoaded('method', fn () => new SupportMethodResource($this->method)),

            'donor' => [
                'name' => $this->is_anonymous ? null : $this->donor_name,
                'email' => $this->donor_email,
                'phone' => $this->donor_phone,
                'is_anonymous' => $this->is_anonymous,
                'contact_preference' => $this->contact_preference,
                'contact_value' => $this->contact_value,
            ],

            'transfer' => [
                'reference' => $this->transfer_reference,
                'date' => $this->transfer_date?->toDateString(),
                'sender_name' => $this->sender_name,
                'proofs_count' => $this->whenLoaded('proofs', fn () => $this->proofs->count()),
            ],

            'team' => [
                'message' => $this->message,
                'major' => $this->whenLoaded('major', fn () => $this->major ? [
                    'uuid' => $this->major->uuid,
                    'name' => $this->major->getTranslations('name'),
                ] : null),
                'member' => $this->whenLoaded('teamMember', fn () => $this->teamMember ? [
                    'uuid' => $this->teamMember->uuid,
                    'name' => $this->teamMember->getTranslations('name'),
                    'photo_url' => $this->teamMember->photo_url,
                ] : null),
            ],

            'rejection_reason' => $this->when($this->status === 'rejected', $this->rejection_reason),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
