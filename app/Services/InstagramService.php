<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    /**
     * Fetch the latest Reels from the configured Instagram Business/Creator account.
     * Credentials come from the dashboard Settings first, then fall back to .env config.
     *
     * @return array<int, array<string, mixed>>
     */
    public function reels(int $limit = 12, bool $bypassCache = false): array
    {
        if (! $this->userId() || ! $this->token()) {
            return [];
        }

        $ttl = (int) $this->config('cache_ttl', config('services.instagram.cache_ttl', 300));

        $fetch = fn () => $this->request($limit);

        // "Live" when ttl = 0 (or a manual refresh), otherwise a short cache to protect
        // against Instagram rate limits (~200 calls/hour).
        if ($ttl <= 0 || $bypassCache) {
            Cache::forget("instagram.reels.{$limit}");

            return $fetch();
        }

        return Cache::remember("instagram.reels.{$limit}", $ttl, $fetch);
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

        try {
            $response = Http::timeout(10)
                ->get("https://graph.facebook.com/{$version}/{$userId}/media", [
                    'fields' => 'id,caption,media_type,media_product_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count,comments.limit(30){text,username,timestamp,like_count}',
                    'limit' => max($limit * 2, 25), // over-fetch, then filter to reels only
                    'access_token' => $token,
                ]);

            if ($response->failed()) {
                Log::warning('Instagram API error', ['body' => $response->json()]);

                return [];
            }

            return collect($response->json('data', []))
                ->filter(fn ($item) => ($item['media_product_type'] ?? null) === 'REELS')
                ->take($limit)
                ->map(fn ($item) => [
                    'id' => $item['id'] ?? null,
                    'caption' => $item['caption'] ?? '',
                    'thumbnail' => $item['thumbnail_url'] ?? ($item['media_url'] ?? null),
                    'video_url' => $item['media_url'] ?? null,
                    'permalink' => $item['permalink'] ?? null,
                    'likes' => $item['like_count'] ?? 0,
                    'comments' => $item['comments_count'] ?? 0,
                    'comment_items' => collect($item['comments']['data'] ?? [])
                        ->map(fn ($c) => [
                            'name' => $c['username'] ?? 'مستخدم',
                            'text' => $c['text'] ?? '',
                            'likes' => $c['like_count'] ?? 0,
                            'time' => $c['timestamp'] ?? null,
                        ])
                        ->all(),
                    'posted_at' => $item['timestamp'] ?? null,
                ])
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::error('Instagram fetch failed', ['message' => $e->getMessage()]);

            return [];
        }
    }
}
