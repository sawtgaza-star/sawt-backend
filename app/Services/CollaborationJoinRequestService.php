<?php

namespace App\Services;

use App\Enums\CollaborationTypeKey;
use App\Models\CollaborationJoinRequest;
use App\Notifications\CollaborationJoinAcceptedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CollaborationJoinRequestService
{
    private const ATTACHMENT_DISK = 'public';

    private const ATTACHMENT_DIRECTORY = 'collaboration-join-requests/attachments';

    public ?string $lastEmailError = null;

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitSponsorship(array $data, ?UploadedFile $attachment = null): CollaborationJoinRequest
    {
        return $this->submit(
            CollaborationTypeKey::Sponsorship,
            [
                'company_name' => $data['company_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'country_code' => $data['country_code'] ?? null,
                'website' => $data['website'] ?? null,
                'payload' => [
                    'support_types' => $data['support_types'],
                    'organization_bio' => $data['organization_bio'],
                    'conditions_notes' => $data['conditions_notes'] ?? null,
                    'additional_notes' => $data['additional_notes'] ?? null,
                ],
            ],
            $attachment,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitPartnership(array $data, ?UploadedFile $attachment = null): CollaborationJoinRequest
    {
        return $this->submit(
            CollaborationTypeKey::Partnership,
            [
                'company_name' => $data['company_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'country_code' => $data['country_code'] ?? null,
                'website' => $data['website'] ?? null,
                'payload' => [
                    'partnership_types' => $data['partnership_types'],
                    'partnership_goal' => $data['partnership_goal'],
                    'additional_notes' => $data['additional_notes'] ?? null,
                ],
            ],
            $attachment,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitCreator(array $data, ?UploadedFile $attachment = null): CollaborationJoinRequest
    {
        return $this->submit(
            CollaborationTypeKey::Creator,
            [
                'company_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'country_code' => $data['country_code'] ?? null,
                'website' => null,
                'payload' => [
                    'full_name' => $data['full_name'],
                    'content_types' => $data['content_types'],
                    'followers_count' => $data['followers_count'],
                    'content_bio' => $data['content_bio'],
                    'socials' => $data['socials'] ?? [],
                    'additional_notes' => $data['additional_notes'] ?? null,
                    'terms_accepted' => (bool) $data['terms_accepted'],
                ],
            ],
            $attachment,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitOther(array $data, ?UploadedFile $attachment = null): CollaborationJoinRequest
    {
        return $this->submit(
            CollaborationTypeKey::Other,
            [
                'company_name' => $data['company_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'country_code' => $data['country_code'] ?? null,
                'website' => null,
                'payload' => [
                    'name' => $data['company_name'],
                    'collaboration_idea' => $data['collaboration_idea'],
                    'additional_notes' => $data['additional_notes'] ?? null,
                ],
            ],
            $attachment,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(CollaborationTypeKey|string $type, array $data, ?UploadedFile $attachment = null): CollaborationJoinRequest
    {
        $typeKey = $type instanceof CollaborationTypeKey ? $type : CollaborationTypeKey::from((string) $type);
        $email = Str::lower(trim((string) $data['email']));

        $attachmentPath = $this->storeAttachment($attachment);

        $payload = [
            'type' => $typeKey,
            'company_name' => $data['company_name'] ?? null,
            'email' => $email,
            'phone' => $data['phone'],
            'country_code' => $data['country_code'] ?? null,
            'website' => $data['website'] ?? null,
            'payload' => $data['payload'] ?? null,
            'status' => 'pending',
            'admin_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];

        if ($attachmentPath !== null) {
            $payload['attachment'] = $attachmentPath;
        }

        $existing = CollaborationJoinRequest::query()
            ->ofType($typeKey)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereIn('status', ['pending', 'rejected'])
            ->latest('id')
            ->first();

        if ($existing) {
            if ($attachmentPath !== null && $existing->attachment) {
                Storage::disk(self::ATTACHMENT_DISK)->delete($existing->attachment);
            }

            $existing->update($payload);

            return $existing->fresh();
        }

        return CollaborationJoinRequest::create($payload);
    }

    public function approve(CollaborationJoinRequest $request, ?int $reviewerId = null, bool $sendEmail = true): CollaborationJoinRequest
    {
        $request->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);

        $request = $request->fresh();

        $this->lastEmailError = null;

        if ($sendEmail) {
            $this->sendAcceptedEmail($request);
        }

        return $request;
    }

    public function reject(CollaborationJoinRequest $request, ?int $reviewerId = null, ?string $adminNote = null): CollaborationJoinRequest
    {
        $request->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'admin_note' => $adminNote,
        ]);

        return $request->fresh();
    }

    protected function storeAttachment(?UploadedFile $attachment): ?string
    {
        if ($attachment === null) {
            return null;
        }

        $path = Storage::disk(self::ATTACHMENT_DISK)->putFile(self::ATTACHMENT_DIRECTORY, $attachment);

        return $path === false ? null : $path;
    }

    protected function sendAcceptedEmail(CollaborationJoinRequest $request): void
    {
        try {
            Notification::route('mail', $request->email)
                ->notify(new CollaborationJoinAcceptedNotification($request));
        } catch (\Throwable $e) {
            $this->lastEmailError = $e->getMessage();
            Log::error('Failed to send collaboration join accepted email.', [
                'email' => $request->email,
                'request_uuid' => $request->uuid,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
