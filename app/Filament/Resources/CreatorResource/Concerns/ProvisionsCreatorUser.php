<?php

namespace App\Filament\Resources\CreatorResource\Concerns;

use App\Models\User;
use App\Services\CreatorJoinRequestService;
use Filament\Notifications\Notification;

trait ProvisionsCreatorUser
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function stripVirtualFields(array $data): array
    {
        unset(
            $data['account_name'],
            $data['account_email'],
            $data['account_phone'],
            $data['account_password'],
            $data['socials'],
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateCreatorFormData(array $data): array
    {
        $service = app(CreatorJoinRequestService::class);

        $name = trim((string) ($data['account_name'] ?? ''));
        $email = (string) ($data['account_email'] ?? '');
        $phone = filled($data['account_phone'] ?? null) ? (string) $data['account_phone'] : null;
        $password = filled($data['account_password'] ?? null) ? (string) $data['account_password'] : null;

        $existingUser = $this->record?->user_id
            ? User::query()->find($this->record->user_id)
            : null;

        if ($existingUser) {
            $user = $service->updateUserForCreatorProfile($existingUser, $name, $email, $phone, $password);
        } else {
            $user = $service->createUserForCreatorProfile($name, $email, $phone, $password);
        }

        $data['user_id'] = $user->id;

        return $this->stripVirtualFields($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function fillCreatorVirtualFields(array $data): array
    {
        if ($this->record->user) {
            $data['account_name'] = $this->record->user->name;
            $data['account_email'] = $this->record->user->email;
            $data['account_phone'] = $this->record->user->phone;
        }

        $data['socials'] = $this->record->socials()
            ->orderBy('display_order')
            ->get()
            ->map(fn ($social) => [
                'platform' => $social->platform,
                'url' => $social->url,
                'followers_count' => $social->followers_count,
                'display_order' => $social->display_order,
            ])
            ->all();

        return $data;
    }

    protected function afterCreatorSaved(): void
    {
        $this->syncCreatorSocials();

        if ($this->record->user_id) {
            $user = User::query()->find($this->record->user_id);

            if ($user) {
                $this->notifyContentCreatorType($user);
            }
        }
    }

    protected function syncCreatorSocials(): void
    {
        $socials = $this->data['socials'] ?? [];

        if (! is_array($socials)) {
            return;
        }

        $this->record->socials()->delete();

        foreach ($socials as $index => $social) {
            if (! is_array($social)) {
                continue;
            }

            if (blank($social['platform'] ?? null) || blank($social['url'] ?? null)) {
                continue;
            }

            $this->record->socials()->create([
                'platform' => $social['platform'],
                'url' => $social['url'],
                'followers_count' => (int) ($social['followers_count'] ?? 0),
                'display_order' => (int) ($social['display_order'] ?? $index),
            ]);
        }
    }

    protected function notifyContentCreatorType(User $user): void
    {
        Notification::make()
            ->title(__('تم حفظ حساب صانع المحتوى'))
            ->body($user->email)
            ->success()
            ->send();
    }
}
