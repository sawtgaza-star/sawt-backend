<?php

namespace App\Services;

use App\Models\Creator;
use App\Models\CreatorJoinRequest;
use App\Models\CreatorSocial;
use App\Models\User;
use App\Notifications\CreatorJoinAcceptedNotification;
use App\Support\ContentCreatorPermissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreatorJoinRequestService
{
    public ?string $lastEmailError = null;

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data): CreatorJoinRequest
    {
        $email = Str::lower(trim((string) $data['email']));

        $this->assertEmailCanJoin($email);

        $payload = [
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'country_code' => $data['country_code'] ?? null,
            'email' => $email,
            'content_types' => $data['content_types'],
            'followers_count' => $data['followers_count'],
            'content_bio' => $data['content_bio'],
            'socials' => $data['socials'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'admin_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];

        $existing = CreatorJoinRequest::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereIn('status', ['pending', 'rejected'])
            ->latest('id')
            ->first();

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return CreatorJoinRequest::create($payload);
    }

    public function assertEmailCanJoin(string $email): void
    {
        $email = Str::lower(trim($email));

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user && $this->userIsContentCreator($user)) {
            throw ValidationException::withMessages([
                'email' => ['هذا البريد مسجّل مسبقاً كصانع محتوى.'],
            ]);
        }
    }

    public function approve(CreatorJoinRequest $request, ?int $reviewerId = null, bool $sendEmail = true): Creator
    {
        $plainPassword = null;

        $creator = DB::transaction(function () use ($request, &$plainPassword) {
            [$creator, $plainPassword] = $this->provisionCreator($request);

            return $creator;
        });

        $this->lastEmailError = null;

        if ($sendEmail) {
            $this->sendAcceptedEmail($creator, $request, $plainPassword);
        }

        $request->deleteQuietly();

        return $creator;
    }

    public function changeStatus(CreatorJoinRequest $request, string $status, ?int $reviewerId = null, ?string $adminNote = null): CreatorJoinRequest
    {
        if (! in_array($status, ['pending', 'approved', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'status' => ['حالة غير صالحة.'],
            ]);
        }

        $needsProvisioning = $status === 'approved'
            && ($request->status !== 'approved' || ! $request->creator_id);

        if ($needsProvisioning) {
            $this->approve($request, $reviewerId);

            return $request;
        }

        $payload = [
            'status' => $status,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ];

        if ($adminNote !== null) {
            $payload['admin_note'] = $adminNote;
        }

        $request->update($payload);

        return $request->fresh();
    }

    public function deleteLinkedProfiles(CreatorJoinRequest $request): void
    {
        $request->loadMissing('creator.user');

        $creator = $request->creator;
        $user = $creator?->user;

        if (! $user) {
            $user = User::query()->whereRaw('LOWER(email) = ?', [Str::lower($request->email)])->first();
            $creator = $creator ?: $user?->creator;
        }

        if ($request->creator_id) {
            $request->forceFill(['creator_id' => null])->saveQuietly();
        }

        if ($creator) {
            $creator->socials()->delete();
            $creator->partnerCompanies()->detach();
            $creator->forceDelete();
        }

        if ($user && ! $user->isAdmin()) {
            $user->delete();
        }
    }

    /**
     * Create profiles for join requests that were accepted before provisioning existed.
     */
    public function backfillApproved(): int
    {
        $count = 0;

        CreatorJoinRequest::query()
            ->where('status', 'approved')
            ->get()
            ->each(function (CreatorJoinRequest $request) use (&$count) {
                if ($request->creator_id && $request->creator) {
                    $request->deleteQuietly();
                } else {
                    $this->approve($request, $request->reviewed_by, sendEmail: false);
                }
                $count++;
            });

        return $count;
    }

    public function promoteUserToContentCreator(User $user): void
    {
        if ($user->isFilamentAdmin()) {
            return;
        }

        $user->forceFill(['type' => User::TYPE_CONTENT_CREATOR])->save();
        $user->syncRoles([$this->ensureContentCreatorRole()]);
    }

    /**
     * @return array{0: Creator, 1: ?string}
     */
    protected function provisionCreator(CreatorJoinRequest $request): array
    {
        $user = User::query()->whereRaw('LOWER(email) = ?', [Str::lower($request->email)])->first();

        if ($user?->isFilamentAdmin()) {
            throw ValidationException::withMessages([
                'email' => ['لا يمكن تحويل حساب إداري إلى صانع محتوى.'],
            ]);
        }

        if ($user && $this->userIsContentCreator($user)) {
            throw ValidationException::withMessages([
                'email' => ['هذا البريد مسجّل مسبقاً كصانع محتوى.'],
            ]);
        }

        $plainPassword = null;

        if (! $user) {
            $plainPassword = Str::password(12);
            $user = User::create([
                'name' => $request->full_name,
                'email' => Str::lower($request->email),
                'phone' => $request->phone,
                'country_code' => $request->country_code,
                'password' => $plainPassword,
                'status' => 'active',
                'type' => User::TYPE_CONTENT_CREATOR,
            ]);
        } else {
            $user->fill([
                'name' => $request->full_name ?: $user->name,
                'phone' => $request->phone ?: $user->phone,
                'country_code' => $request->country_code ?: $user->country_code,
            ])->save();
        }

        $this->promoteUserToContentCreator($user->fresh());

        $profile = [
            'bio' => $this->localized((string) $request->content_bio),
            'role' => $this->localized($this->roleFromTypes($request->content_types ?? [])),
            'followers_count' => $request->followers_count ?? 0,
            'status' => 'active',
        ];

        $creator = Creator::create([
            'user_id' => $user->id,
            'username' => $this->uniqueUsername($request->full_name, $request->email),
            ...$profile,
        ]);

        $this->syncSocials($creator, $request->socials ?? []);

        return [$creator, $plainPassword];
    }

    protected function sendAcceptedEmail(Creator $creator, CreatorJoinRequest $request, ?string $plainPassword): void
    {
        $user = $creator->user ?? $creator->load('user')->user;

        if (! $user?->email) {
            return;
        }

        try {
            $user->notify(new CreatorJoinAcceptedNotification($request, $plainPassword));
        } catch (\Throwable $e) {
            $this->lastEmailError = $e->getMessage();
            Log::error('Failed to send creator join accepted email.', [
                'email' => $user->email,
                'join_request_id' => $request->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function userIsContentCreator(User $user): bool
    {
        return $user->type === User::TYPE_CONTENT_CREATOR
            || $user->hasRole(User::ROLE_CONTENT_CREATOR)
            || $user->creator()->exists();
    }

    /**
     * @return array{ar: string, en: string}|null
     */
    protected function localized(?string $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        return ['ar' => $value, 'en' => $value];
    }

    /**
     * @param  list<string>  $types
     */
    protected function roleFromTypes(array $types): ?string
    {
        $first = $types[0] ?? null;

        return $first ? (string) $first : null;
    }

    /**
     * @param  list<array{platform?: string, url?: string}>  $socials
     */
    protected function syncSocials(Creator $creator, array $socials): void
    {
        $order = 0;

        foreach ($socials as $social) {
            $platform = $social['platform'] ?? null;
            $url = $social['url'] ?? null;

            if (! $platform || ! $url || ! in_array($platform, CreatorJoinRequest::PLATFORMS, true)) {
                continue;
            }

            CreatorSocial::query()->firstOrCreate(
                [
                    'creator_id' => $creator->id,
                    'platform' => $platform,
                    'url' => $url,
                ],
                [
                    'display_order' => $order++,
                ]
            );
        }
    }

    protected function uniqueUsername(string $fullName, string $email): string
    {
        $base = Str::slug($fullName, '_');

        if ($base === '') {
            $base = Str::slug(Str::before($email, '@'), '_');
        }

        if ($base === '') {
            $base = 'creator';
        }

        $username = $base;
        $i = 1;

        while (Creator::withTrashed()->where('username', $username)->exists()) {
            $username = $base.'_'.$i++;
        }

        return $username;
    }

    protected function ensureContentCreatorRole(): Role
    {
        $permissions = ContentCreatorPermissions::all();

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => User::ROLE_CONTENT_CREATOR, 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        return $role;
    }
}
