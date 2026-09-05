<?php

namespace App\Services;

use App\Models\MediaConsultationRequest;
use App\Models\MediaServiceItem;
use App\Notifications\MediaConsultationAcceptedNotification;
use App\Notifications\MediaConsultationRejectedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Submit / approve / reject Sawt Media consultation bookings («احجز استشارتك»).
 */
class MediaConsultationRequestService
{
    /** Last mail failure message (surfaced in Filament after approve/reject). */
    public ?string $lastEmailError = null;

    /**
     * Store a new pending consultation from the public form.
     *
     * @param  array{name: string, email: string, phone: string, country_code?: ?string, service: string}  $data
     */
    public function submit(array $data): MediaConsultationRequest
    {
        $serviceKey = Str::lower(trim((string) $data['service']));
        $service = MediaServiceItem::query()
            ->where('is_active', true)
            ->where(function ($q) use ($serviceKey) {
                $q->where('slug', $serviceKey)->orWhere('uuid', $serviceKey);
            })
            ->firstOrFail();

        return MediaConsultationRequest::create([
            'name' => trim((string) $data['name']),
            'email' => Str::lower(trim((string) $data['email'])),
            'phone' => trim((string) $data['phone']),
            'country_code' => $data['country_code'] ?? null,
            'media_service_id' => $service->id,
            'service_slug' => $service->slug,
            // Prefer Arabic title for admin inbox / emails (fallback EN)
            'service_title' => $service->getTranslation('title', 'ar')
                ?: $service->getTranslation('title', 'en')
                ?: $service->slug,
            'status' => 'pending',
        ]);
    }

    /**
     * Accept request and email the applicant.
     */
    public function approve(MediaConsultationRequest $request, ?int $reviewerId = null, bool $sendEmail = true): MediaConsultationRequest
    {
        $request->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'admin_note' => null,
        ]);

        $request = $request->fresh();
        $this->lastEmailError = null;

        if ($sendEmail) {
            $this->sendDecisionEmail($request, approved: true);
        }

        return $request;
    }

    /**
     * Reject request (optional reason) and email the applicant.
     */
    public function reject(
        MediaConsultationRequest $request,
        ?int $reviewerId = null,
        ?string $adminNote = null,
        bool $sendEmail = true,
    ): MediaConsultationRequest {
        $request->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'admin_note' => $adminNote,
        ]);

        $request = $request->fresh();
        $this->lastEmailError = null;

        if ($sendEmail) {
            $this->sendDecisionEmail($request, approved: false);
        }

        return $request;
    }

    protected function sendDecisionEmail(MediaConsultationRequest $request, bool $approved): void
    {
        try {
            $notification = $approved
                ? new MediaConsultationAcceptedNotification($request)
                : new MediaConsultationRejectedNotification($request);

            Notification::route('mail', $request->email)->notify($notification);
        } catch (\Throwable $e) {
            $this->lastEmailError = $e->getMessage();
            Log::error('Failed to send media consultation decision email.', [
                'email' => $request->email,
                'request_uuid' => $request->uuid,
                'approved' => $approved,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
