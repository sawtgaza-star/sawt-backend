<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    public const STATUS_OK = 'ok';

    public const STATUS_EMPTY = 'empty';

    public const STATUS_MISSING_CREDENTIALS = 'missing_credentials';

    public const STATUS_TOKEN_EXPIRED = 'token_expired';

    public const STATUS_API_ERROR = 'api_error';

    protected string $lastStatus = self::STATUS_MISSING_CREDENTIALS;

    protected ?string $lastMessage = null;

    /**
     * Fetch the latest Reels from the configured Instagram Business/Creator account.
     * Credentials come from the dashboard Settings first, then fall back to .env config.
     *
     * @return array<int, array<string, mixed>>
     */
    public function reels(int $limit = 12, bool $bypassCache = false): array
    {
        $this->lastStatus = self::STATUS_MISSING_CREDENTIALS;
        $this->lastMessage = null;

        if (! $this->isConfigured()) {
            $this->lastMessage = 'Instagram user id or access token is missing.';

            return [];
        }

        $ttl = (int) $this->config('cache_ttl', config('services.instagram.cache_ttl', 300));
        $cacheKey = "instagram.reels.v3.{$limit}";

        $fetch = fn () => $this->request($limit);

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

    public function isConfigured(): bool
    {
        return filled($this->userId()) && filled($this->token());
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
     * @return array<int, array<string, mixed>>
     */
    protected function request(int $limit): array
    {
        $version = config('services.instagram.version', 'v21.0');
        $userId = $this->userId();
        $token = $this->token();
        $limit = max(1, $limit);

        try {
            $raw = [];
            $url = "https://graph.facebook.com/{$version}/{$userId}/media";
            $params = [
                'fields' => 'id,caption,media_type,media_product_type,media_url,thumbnail_url,permalink,timestamp,username,like_count,comments_count,comments.limit(30){text,username,timestamp,like_count}',
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
                ->map(function (array $item) use ($version, $token) {
                    $id = $item['id'] ?? null;
                    $insights = $id
                        ? $this->insights((string) $id, (string) $version, (string) $token)
                        : ['views' => null, 'reach' => null];

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
                                'name' => $c['username'] ?? 'مستخدم',
                                'text' => $c['text'] ?? '',
                                'likes' => $c['like_count'] ?? 0,
                                'time' => $c['timestamp'] ?? null,
                            ])
                            ->all(),
                        'collaborators' => $id
                            ? $this->collaborators((string) $id, (string) $version, (string) $token)
                            : [],
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
            $response = Http::timeout(10)
                ->get("https://graph.facebook.com/{$version}/{$mediaId}/insights", [
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
            $response = Http::timeout(10)
                ->get("https://graph.facebook.com/{$version}/{$mediaId}/collaborators", [
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
