<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\MediaUrl;

class ContentPageService
{
    public function __construct(
        protected SettingRepositoryInterface $settings,
        protected InstagramService $instagram,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function page(): array
    {
        return [
            'hero' => $this->hero(),
            'reels' => $this->reels(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function hero(): array
    {
        $items = $this->settings->get('content_hero_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'image_url' => MediaUrl::make($this->settings->get('content_header_bg')),
            'title' => $this->settings->i18n(
                'content_hero_title',
                'كل فكرة إلها صوت... وصوت بيجمعهم',
                'Every idea has a voice… and Sawt brings them together'
            ),
            'description' => $this->settings->i18n('content_hero_desc'),
            'items' => collect(array_values($items))->map(fn (array $item, int $index) => [
                'image_url' => MediaUrl::make($item['image'] ?? null),
                'sort_order' => $index,
            ])->filter(fn (array $item) => filled($item['image_url']))->values()->all(),
        ];
    }

    /**
     * Instagram reels for the content page (same source as GET /api/v1/reels).
     *
     * @return array<string, mixed>
     */
    protected function reels(): array
    {
        $limit = (int) ($this->settings->get('content_most_viewed_limit', 6) ?: 6);
        $limit = max(1, min(30, $limit));

        $status = 'missing_credentials';
        $items = [];

        if ($this->instagram->isConfigured()) {
            $fetched = $this->instagram->reels($limit);

            $items = collect($fetched)
                ->map(fn (array $reel, int $index) => [
                    'id' => $reel['id'] ?? null,
                    'caption' => (string) ($reel['caption'] ?? ''),
                    'thumbnail' => $reel['thumbnail'] ?? null,
                    'video_url' => (string) ($reel['video_url'] ?? ''),
                    'permalink' => (string) ($reel['permalink'] ?? ''),
                    'username' => $reel['username'] ?? null,
                    'likes' => $reel['likes'] ?? 0,
                    'comments_count' => $reel['comments'] ?? 0,
                    'views' => $reel['views'] ?? null,
                    'reach' => $reel['reach'] ?? null,
                    'collaborators' => $reel['collaborators'] ?? [],
                    'posted_at' => $reel['posted_at'] ?? null,
                    'sort_order' => $index,
                ])
                ->values()
                ->all();

            $status = $items === [] ? 'empty' : 'ok';
        }

        return [
            'title' => $this->settings->i18n('content_most_viewed_title', 'الأكثر مشاهدة', 'Most viewed'),
            'view_more' => $this->settings->i18n('content_most_viewed_more', 'رؤية المزيد', 'See more'),
            'status' => $status,
            'items' => $items,
        ];
    }
}
