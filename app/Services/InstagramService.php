<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches Instagram Reels via the Instagram Graph API for Settings, home, and content APIs.
 * Credentials come from dashboard Settings first, then fall back to .env config.
 * Master switch: Settings → ريلز إنستغرام → reels_enabled (off = no Graph calls site-wide).
 */
class InstagramService
{
    public const STATUS_OK = 'ok';

    public const STATUS_EMPTY = 'empty';

    public const STATUS_MISSING_CREDENTIALS = 'missing_credentials';

    public const STATUS_TOKEN_EXPIRED = 'token_expired';

    public const STATUS_API_ERROR = 'api_error';

    /** Settings reels_enabled is off — callers must not treat this as a Graph failure. */
    public const STATUS_DISABLED = 'disabled';

    protected string $lastStatus = self::STATUS_MISSING_CREDENTIALS;

    protected ?string $lastMessage = null;

    /**
     * Fetch the latest Reels from the configured Instagram Business/Creator account.
     *
     * @param  bool  $withExtras  Per-reel insights/collaborators (slow). Keep false for admin preview.
     * @return array<int, array<string, mixed>>
     */
    public function reels(int $limit = 12, bool $bypassCache = false, bool $withExtras = false): array
    {
        $this->lastStatus = self::STATUS_MISSING_CREDENTIALS;
        $this->lastMessage = null;

        // Global kill switch — every API/page that uses this method skips Graph when off
        if (! $this->isEnabled()) {
            $this->lastStatus = self::STATUS_DISABLED;
            $this->lastMessage = 'Instagram reels are disabled in Settings (reels_enabled).';

            return [];
        }

        if (! $this->isConfigured()) {
            $this->lastMessage = 'Instagram user id or access token is missing.';

            return [];
        }

        // Soft expiry: Meta long-lived tokens last ~60 days; we track save time in Settings
        if ($this->isTokenPastLocalExpiry()) {
            $this->lastStatus = self::STATUS_TOKEN_EXPIRED;
            $expiresAt = $this->tokenExpiresAt();
            $this->lastMessage = $expiresAt
                ? 'Instagram access token expired on '.$expiresAt->toDateString().'. Save a new long-lived token in Settings.'
                : 'Instagram access token has expired. Save a new long-lived token in Settings.';

            return [];
        }

        $ttl = (int) $this->config('cache_ttl', config('services.instagram.cache_ttl', 300));
        // Separate lite/full caches so admin preview stays fast
        // Key includes account + token so switching accounts never serves another account's cached media
        $cacheKey = $this->cacheKey($limit, $withExtras);

        $fetch = fn () => $this->request($limit, $withExtras);

        if ($ttl <= 0 || $bypassCache) {
            Cache::forget($cacheKey);

            return $fetch();
        }

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            // Never keep an empty failure stuck in cache.
            if (is_array($cached) && $cached !== []) {
                $this->lastStatus = self::STATUS_OK;
                $this->lastMessage = null;

                return $cached;
            }

            Cache::forget($cacheKey);
        }

        $reels = $fetch();

        if ($reels !== []) {
            Cache::put($cacheKey, $reels, $ttl);
        }

        return $reels;
    }

    public function lastStatus(): string
    {
        return $this->lastStatus;
    }

    public function lastMessage(): ?string
    {
        return $this->lastMessage;
    }

    /**
     * Drop known Instagram reels cache keys (after token rotate / clear).
     */
    public function forgetReelsCache(): void
    {
        foreach ([3, 6, 8, 12, 24, 50] as $limit) {
            Cache::forget($this->cacheKey($limit, false));
            Cache::forget($this->cacheKey($limit, true));
        }
    }

    /**
     * Reels from the platform Instagram account where $username is an accepted collaborator.
     * Used on creator detail «المحتوى» — matches Creator Instagram social URL/username.
     *
     * @return array<int, array<string, mixed>>
     */
    public function reelsForCollaborator(string $username, int $limit = 12): array
    {
        $needle = $this->normalizeInstagramUsername($username);
        if ($needle === '') {
            $this->lastStatus = self::STATUS_EMPTY;
            $this->lastMessage = 'Instagram collaborator username is empty.';

            return [];
        }

        $limit = max(1, min(12, $limit));

        // Same limit + extras as GET /api/v1/reels
        $pool = $this->reels($limit, bypassCache: false, withExtras: true);

        if ($pool === []) {
            return [];
        }

        $matched = collect($pool)
            ->filter(fn (array $reel) => $this->reelHasCollaborator($reel, $needle))
            ->take($limit)
            ->values()
            ->all();

        if ($matched === []) {
            $this->lastStatus = self::STATUS_EMPTY;
            $this->lastMessage = 'No reels found where this username is a collaborator.';
        }

        return $matched;
    }

    /**
     * Strip @ and path noise from an Instagram handle or profile URL.
     */
    public function normalizeInstagramUsername(?string $raw): string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return '';
        }

        // Full profile URL → last path segment
        if (str_contains($raw, 'instagram.com')) {
            $path = (string) parse_url($raw, PHP_URL_PATH);
            $raw = trim($path, '/');
            // Drop trailing segments like /reel/… if a bad URL was stored
            $raw = explode('/', $raw)[0] ?? '';
        }

        $raw = ltrim($raw, '@');
        $raw = strtok($raw, '?#') ?: $raw;

        return strtolower(trim($raw));
    }

    /**
     * @param  array<string, mixed>  $reel
     */
    protected function reelHasCollaborator(array $reel, string $needleUsername): bool
    {
        foreach ($reel['collaborators'] ?? [] as $collaborator) {
            if (! is_array($collaborator)) {
                continue;
            }

            $handle = $this->normalizeInstagramUsername($collaborator['username'] ?? '');
            if ($handle === '' || $handle !== $needleUsername) {
                continue;
            }

            // Prefer accepted invites; treat missing status as accepted (API sometimes omits it)
            $status = strtolower((string) ($collaborator['invite_status'] ?? ''));
            if ($status === '' || $status === 'accepted') {
                return true;
            }
        }

        return false;
    }

    public function isConfigured(): bool
    {
        return filled($this->userId()) && filled($this->token());
    }

    /**
     * Master toggle from Settings → ريلز إنستغرام → «تفعيل عرض الريلز».
     * When false, reels()/reelsForCollaborator() never hit Instagram Graph.
     */
    public function isEnabled(): bool
    {
        return (bool) Setting::get('reels_enabled', false);
    }

    /**
     * When the dashboard token was last saved (null if unknown / .env-only).
     */
    public function tokenSavedAt(): ?CarbonInterface
    {
        $raw = Setting::get('instagram_access_token_saved_at');
        if (! filled($raw)) {
            return null;
        }

        try {
            return Carbon::parse((string) $raw);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Ask Meta for the token's real expiry (debug_token) and store it, so a
     * short-lived Explorer token isn't mistaken for a 60-day one.
     * Instagram-Login tokens have no debug_token — those keep the local 60-day estimate.
     *
     * Returns null when Meta couldn't be asked (network / permission error).
     *
     * @return array{valid: bool, expires_at: ?CarbonInterface, never_expires: bool, error: ?string}|null
     */
    public function syncTokenExpiry(): ?array
    {
        $token = $this->token();

        if (! filled($token) || $this->isInstagramLoginToken()) {
            Setting::set('instagram_access_token_expires_at', '', group: 'reels', type: 'string');

            return null;
        }

        try {
            // A token may inspect itself; an expired one fails with code 190.
            $response = Http::timeout(8)->get('https://graph.facebook.com/'.config('services.instagram.version', 'v21.0').'/debug_token', [
                'input_token' => $token,
                'access_token' => $token,
            ]);
        } catch (\Throwable) {
            return null;
        }

        $data = $response->json('data');
        $error = $response->json('error');

        if (is_array($error) || ! is_array($data)) {
            if ((int) ($error['code'] ?? 0) !== 190) {
                return null; // network / permission problem — don't guess
            }

            $result = ['valid' => false, 'expires_at' => now(), 'never_expires' => false, 'error' => (string) ($error['message'] ?? '')];
        } elseif (! ($data['is_valid'] ?? false)) {
            $result = ['valid' => false, 'expires_at' => now(), 'never_expires' => false, 'error' => (string) ($data['error']['message'] ?? '')];
        } else {
            $expires = (int) ($data['expires_at'] ?? 0);
            $result = [
                'valid' => true,
                'expires_at' => $expires > 0 ? Carbon::createFromTimestamp($expires) : null,
                'never_expires' => $expires === 0,
                'error' => null,
            ];
        }

        Setting::set(
            'instagram_access_token_expires_at',
            $result['never_expires'] ? 'never' : $result['expires_at']->toIso8601String(),
            group: 'reels',
            type: 'string',
        );

        return $result;
    }

    /** Meta reported this token as non-expiring (e.g. a Page token from a long-lived user token). */
    public function tokenNeverExpires(): bool
    {
        return Setting::get('instagram_access_token_expires_at') === 'never';
    }

    /**
     * Expiry from Meta (debug_token) when known; otherwise saved_at + token_ttl_days (default 60).
     */
    public function tokenExpiresAt(): ?CarbonInterface
    {
        $real = (string) Setting::get('instagram_access_token_expires_at', '');

        if ($real === 'never') {
            return null;
        }

        if ($real !== '') {
            try {
                return Carbon::parse($real);
            } catch (\Throwable) {
                // fall through to the local estimate
            }
        }

        $savedAt = $this->tokenSavedAt();
        if (! $savedAt) {
            return null;
        }

        $days = max(1, (int) config('services.instagram.token_ttl_days', 60));

        return $savedAt->copy()->addDays($days);
    }

    /**
     * True when a saved_at stamp exists and the local TTL has passed.
     */
    public function isTokenPastLocalExpiry(): bool
    {
        $expiresAt = $this->tokenExpiresAt();

        return $expiresAt !== null && $expiresAt->isPast();
    }

    /**
     * Days remaining until local expiry (null if unknown; 0 if already expired).
     */
    public function tokenDaysRemaining(): ?int
    {
        $expiresAt = $this->tokenExpiresAt();
        if (! $expiresAt) {
            return null;
        }

        if ($expiresAt->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($expiresAt, false);
    }

    /**
     * Instagram-Login tokens (IGAA…/IGQV…) only work on graph.instagram.com;
     * Facebook-Login tokens (EAA…) go through graph.facebook.com.
     */
    protected function graphBase(): string
    {
        return $this->isInstagramLoginToken()
            ? 'https://graph.instagram.com'
            : 'https://graph.facebook.com';
    }

    protected function isInstagramLoginToken(): bool
    {
        return str_starts_with((string) $this->token(), 'IG');
    }

    /** Show only reels that appear on the Instagram profile grid (dashboard toggle, default on). */
    protected function profileReelsOnly(): bool
    {
        return (bool) Setting::get('instagram_profile_reels_only', true);
    }

    protected function cacheKey(int $limit, bool $withExtras): string
    {
        return 'instagram.reels.v4.'.md5($this->userId().'|'.$this->token().'|'.(int) $this->profileReelsOnly()).'.'.$limit.'.'.($withExtras ? 'full' : 'lite');
    }

    /**
     * The id we query must be the Instagram professional account, not the Facebook Page.
     * If a Page id was saved (a common mix-up — it returns the Page's Facebook videos
     * or fails), follow its linked instagram_business_account instead.
     */
    protected function resolveInstagramAccountId(): ?string
    {
        $userId = $this->userId();

        if ($this->isInstagramLoginToken()) {
            return $userId ?: 'me';
        }

        if (! $userId) {
            return null;
        }

        return Cache::remember('instagram.account_id.'.md5($userId.'|'.$this->token()), 86400, function () use ($userId) {
            try {
                $response = Http::timeout(8)->get(
                    'https://graph.facebook.com/'.config('services.instagram.version', 'v21.0')."/{$userId}",
                    ['fields' => 'instagram_business_account', 'access_token' => $this->token()],
                );

                $linked = $response->json('instagram_business_account.id');

                if ($response->successful() && filled($linked)) {
                    Log::info('Instagram user id is a Facebook Page; using its linked Instagram account', [
                        'page_id' => $userId,
                        'instagram_id' => $linked,
                    ]);

                    return (string) $linked;
                }
            } catch (\Throwable) {
                // Not a Page (or no permission) — the saved id is already the IG account.
            }

            return $userId;
        });
    }

    protected function userId(): ?string
    {
        return $this->config('user_id', config('services.instagram.user_id')) ?: null;
    }

    protected function token(): ?string
    {
        return $this->config('token', config('services.instagram.token')) ?: null;
    }

    /**
     * Read an instagram setting from the dashboard Settings table, falling back to $default.
     * Setting keys: instagram_user_id, instagram_access_token, instagram_cache_ttl.
     */
    protected function config(string $key, mixed $default = null): mixed
    {
        $map = [
            'user_id' => 'instagram_user_id',
            'token' => 'instagram_access_token',
            'cache_ttl' => 'instagram_cache_ttl',
        ];

        $value = Setting::get($map[$key] ?? $key);

        return ($value === null || $value === '') ? $default : $value;
    }

    /**
     * Call Graph media edge and map REELS into the app shape.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function request(int $limit, bool $withExtras = false): array
    {
        $version = config('services.instagram.version', 'v21.0');
        $userId = $this->resolveInstagramAccountId();
        $token = $this->token();
        $limit = max(1, $limit);

        try {
            $raw = [];
            $url = $this->graphBase()."/{$version}/{$userId}/media";
            $params = [
                // Nested comments keep the list call lighter (avoid comments.limit(30))
                'fields' => 'id,caption,media_type,media_product_type,is_shared_to_feed,media_url,thumbnail_url,permalink,timestamp,username,like_count,comments_count,comments.limit(10){id,text,username,timestamp,like_count}',
                'limit' => 50,
                    'access_token' => $token,
            ];

            // Paginate until we have enough REELS (or pages run out).
            for ($page = 0; $page < 5; $page++) {
                $response = Http::timeout(15)->get($url, $params);

            if ($response->failed()) {
                    $this->recordApiFailure($response->status(), $response->json(), $userId);

                    break;
                }

                $batch = $response->json('data', []);
                if ($batch === []) {
                    break;
                }

                $raw = array_merge($raw, $batch);

                $reelCount = collect($raw)->filter(fn ($item) => $this->isReel($item))->count();
                if ($reelCount >= $limit) {
                    break;
                }

                $next = $response->json('paging.next');
                if (! $next) {
                    break;
                }

                // Next page is a full URL — don't re-append params.
                $url = $next;
                $params = [];
            }

            // API already failed — don't overwrite status with "empty".
            if (in_array($this->lastStatus, [self::STATUS_TOKEN_EXPIRED, self::STATUS_API_ERROR], true)) {
                return [];
            }

            $reels = collect($raw)
                ->filter(fn ($item) => $this->isReel($item))
                ->sortByDesc(fn ($item) => $item['timestamp'] ?? '')
                ->take($limit)
                ->values()
                ->map(function (array $item) use ($version, $token, $withExtras) {
                    $id = $item['id'] ?? null;

                    // Extra Graph calls per reel — skip on admin/list to avoid 60s timeouts
                    $insights = ['views' => null, 'reach' => null];
                    $collaborators = [];
                    if ($withExtras && $id) {
                        $insights = $this->insights((string) $id, (string) $version, (string) $token);
                        $collaborators = $this->collaborators((string) $id, (string) $version, (string) $token);
                    }

                    return [
                        'id' => $id,
                    'caption' => $item['caption'] ?? '',
                    'thumbnail' => $item['thumbnail_url'] ?? ($item['media_url'] ?? null),
                    'video_url' => $item['media_url'] ?? null,
                    'permalink' => $item['permalink'] ?? null,
                        'username' => $item['username'] ?? null,
                    'likes' => $item['like_count'] ?? 0,
                    'comments' => $item['comments_count'] ?? 0,
                        'views' => $insights['views'],
                        'reach' => $insights['reach'],
                    'comment_items' => collect($item['comments']['data'] ?? [])
                        ->map(fn ($c) => [
                                'id' => $c['id'] ?? null,
                            'name' => $c['username'] ?? 'مستخدم',
                            'text' => $c['text'] ?? '',
                            'likes' => $c['like_count'] ?? 0,
                            'time' => $c['timestamp'] ?? null,
                        ])
                        ->all(),
                        'collaborators' => $collaborators,
                    'posted_at' => $item['timestamp'] ?? null,
                    ];
                })
                ->all();

            if ($reels === []) {
                $this->lastStatus = self::STATUS_EMPTY;
                $this->lastMessage = $raw === []
                    ? 'Instagram returned no media for this account.'
                    : 'Instagram media returned but none matched the reels filter.';

                if ($raw !== []) {
                    Log::info('Instagram media returned but no reels matched filter', [
                        'sample' => collect($raw)->take(3)->map(fn ($i) => [
                            'id' => $i['id'] ?? null,
                            'media_type' => $i['media_type'] ?? null,
                            'media_product_type' => $i['media_product_type'] ?? null,
                            'permalink' => $i['permalink'] ?? null,
                        ])->all(),
                    ]);
                }

                return [];
            }

            $this->lastStatus = self::STATUS_OK;
            $this->lastMessage = null;

            return $reels;
        } catch (\Throwable $e) {
            Log::error('Instagram fetch failed', ['message' => $e->getMessage()]);
            $this->lastStatus = self::STATUS_API_ERROR;
            $this->lastMessage = $e->getMessage();

            return [];
        }
    }

    /**
     * @param  array<string, mixed>|null  $body
     */
    protected function recordApiFailure(int $status, ?array $body, ?string $userId): void
    {
        $error = is_array($body) ? ($body['error'] ?? null) : null;
        $message = is_array($error) ? (string) ($error['message'] ?? 'Instagram API error') : 'Instagram API error';
        $code = is_array($error) ? (int) ($error['code'] ?? 0) : 0;
        $subcode = is_array($error) ? (int) ($error['error_subcode'] ?? 0) : 0;

        $expired = $code === 190
            || $subcode === 463
            || str_contains(strtolower($message), 'session has expired')
            || str_contains(strtolower($message), 'access token');

        $this->lastStatus = $expired ? self::STATUS_TOKEN_EXPIRED : self::STATUS_API_ERROR;
        $this->lastMessage = $message;

        Log::warning('Instagram API error', [
            'status' => $status,
            'body' => $body,
            'user_id' => $userId,
            'mapped_status' => $this->lastStatus,
        ]);
    }

    /**
     * Media insights (views / reach) for a reel.
     *
     * @return array{views: int|null, reach: int|null}
     */
    protected function insights(string $mediaId, string $version, string $token): array
    {
        $result = ['views' => null, 'reach' => null];

        try {
            $response = Http::timeout(4)
                ->get($this->graphBase()."/{$version}/{$mediaId}/insights", [
                    'metric' => 'views,reach',
                    'access_token' => $token,
                ]);

            if ($response->failed()) {
                Log::info('Instagram insights unavailable', [
                    'media_id' => $mediaId,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return $result;
            }

            foreach ($response->json('data', []) as $row) {
                $name = $row['name'] ?? null;
                $value = $row['values'][0]['value']
                    ?? $row['total_value']['value']
                    ?? null;

                if ($name === 'views' && is_numeric($value)) {
                    $result['views'] = (int) $value;
                }

                if ($name === 'reach' && is_numeric($value)) {
                    $result['reach'] = (int) $value;
                }
            }

            return $result;
        } catch (\Throwable $e) {
            Log::info('Instagram insights fetch failed', [
                'media_id' => $mediaId,
                'message' => $e->getMessage(),
            ]);

            return $result;
        }
    }

    /**
     * Collaborators invited on an IG Media object (Accepted / Pending).
     *
     * @return array<int, array{id: mixed, username: string, invite_status: string}>
     */
    protected function collaborators(string $mediaId, string $version, string $token): array
    {
        try {
            $response = Http::timeout(4)
                ->get($this->graphBase()."/{$version}/{$mediaId}/collaborators", [
                    'fields' => 'id,username,invite_status',
                    'access_token' => $token,
                ]);

            if ($response->failed()) {
                Log::info('Instagram collaborators unavailable', [
                    'media_id' => $mediaId,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return [];
            }

            return collect($response->json('data', []))
                ->map(fn ($c) => [
                    'id' => $c['id'] ?? null,
                    'username' => (string) ($c['username'] ?? ''),
                    'invite_status' => (string) ($c['invite_status'] ?? ''),
                ])
                ->filter(fn (array $c) => $c['username'] !== '' || $c['id'] !== null)
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::info('Instagram collaborators fetch failed', [
                'media_id' => $mediaId,
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function isReel(array $item): bool
    {
        $product = $item['media_product_type'] ?? null;
        $type = $item['media_type'] ?? null;
        $permalink = (string) ($item['permalink'] ?? '');

        // Only real Instagram posts — never Facebook videos/permalinks.
        if (! str_contains(strtolower((string) parse_url($permalink, PHP_URL_HOST)), 'instagram.com')) {
            return false;
        }

        // Reels cross-posted from Facebook / Business Suite live only in the Reels tab
        // (is_shared_to_feed = false) and don't show on the profile grid — skip them by default.
        if ($this->profileReelsOnly() && ($item['is_shared_to_feed'] ?? null) === false) {
            return false;
        }

        if ($product === 'REELS') {
            return true;
        }

        // Fallback: Instagram reel URLs, or VIDEO posts that Graph omits product type for.
        if ($type === 'VIDEO' && (str_contains($permalink, '/reel/') || str_contains($permalink, '/reels/'))) {
            return true;
        }

        return false;
    }
}
