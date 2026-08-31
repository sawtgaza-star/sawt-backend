<?php

namespace App\Services;

use App\Models\Story;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\MediaUrl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class StoryService
{
    public function __construct(
        protected SettingRepositoryInterface $settings,
    ) {}

    /**
     * Homepage stories carousel.
     *
     * @return array<string, mixed>
     */
    public function homepageStories(): array
    {
        $limit = (int) ($this->settings->get('home_stories_limit', 4) ?: 4);
        $limit = max(1, min(12, $limit));

        $featured = Story::query()
            ->published()
            ->featured()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $items = $featured->count() >= $limit
            ? $featured
            : $this->mergeLatest($featured, $limit);

        return [
            'title' => $this->settings->i18n(
                'home_stories_title',
                'هل لديك صوت يستحق أن يُسمع؟',
                'Do you have a voice that deserves to be heard?'
            ),
            'description' => $this->settings->i18n(
                'home_stories_desc',
                'شاركنا قصتك أو قضيتك، وقد تكون القصة القادمة التي نسلط الضوء عليها ليصل صوتها إلى العالم',
                'Share your story or cause — it may be the next one we highlight so its voice reaches the world'
            ),
            'badge' => $this->settings->i18n(
                'home_stories_badge',
                '+100 قصة واقعية نقلتها صوت إلى العالم',
                '+100 real stories Sawt has brought to the world'
            ),
            'view_all' => [
                'label' => $this->settings->i18n('story_view_all', 'عرض جميع القصص', 'View all stories'),
            ],
            'items' => $items,
        ];
    }

    /**
     * @return array{hero: array<string, mixed>, items: LengthAwarePaginator}
     */
    public function listingPage(
        ?string $categorySlug = null,
        ?string $search = null,
        ?int $perPage = null,
    ): array {
        if (filled($categorySlug) && ! $this->categoryExists($categorySlug)) {
            throw (new ModelNotFoundException)->setModel(Story::class, [$categorySlug]);
        }

        $query = Story::query()
            ->published()
            ->when(filled($categorySlug), fn ($q) => $q->withCategorySlug((string) $categorySlug))
            ->when(filled($search), function ($q) use ($search) {
                $term = trim((string) $search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('title->ar', 'like', "%{$term}%")
                        ->orWhere('title->en', 'like', "%{$term}%")
                        ->orWhere('card_headline->ar', 'like', "%{$term}%")
                        ->orWhere('card_headline->en', 'like', "%{$term}%")
                        ->orWhere('excerpt->ar', 'like', "%{$term}%")
                        ->orWhere('excerpt->en', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        $perPage = max(1, min(50, $perPage ?: 12));

        return [
            'hero' => $this->hero(),
            'items' => $query->paginate($perPage),
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ModelNotFoundException
     */
    public function show(string $uuid): array
    {
        $story = Story::query()
            ->published()
            ->with('images')
            ->where('uuid', $uuid)
            ->firstOrFail();

        $story->increment('views_count');

        $slugs = $story->categorySlugs();

        $related = Story::query()
            ->published()
            ->where('uuid', '!=', $story->uuid)
            ->when($slugs !== [], function ($q) use ($slugs) {
                $q->where(function ($inner) use ($slugs) {
                    foreach ($slugs as $slug) {
                        $inner->orWhereRaw(
                            "JSON_SEARCH(categories, 'one', ?, NULL, '$[*].slug') IS NOT NULL",
                            [$slug]
                        );
                    }
                });
            })
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return [
            'hero' => $this->hero(),
            'story' => $story,
            'related' => [
                'title' => $this->settings->i18n('story_related_title', 'قصص ذات صلة', 'Related stories'),
                'subtitle' => $this->settings->i18n(
                    'story_related_subtitle',
                    'قصص حقيقية من غزة نقلتها منصة صوت إلى العالم',
                    'Real stories from Gaza carried by Sawt to the world'
                ),
                'view_all' => [
                    'label' => $this->settings->i18n('story_view_all', 'عرض جميع القصص', 'View all stories'),
                ],
                'items' => $related,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function hero(): array
    {
        return [
            'image_url' => MediaUrl::make($this->settings->get('story_header_bg')),
            'title' => $this->settings->i18n('story_hero_title', 'قصص النجاح', 'Success Stories'),
            'description' => $this->settings->i18n(
                'story_hero_desc',
                'قصص حقيقية من غزة نقلتها منصة صوت إلى العالم',
                'Real stories from Gaza carried by Sawt to the world'
            ),
        ];
    }

    protected function categoryExists(string $slug): bool
    {
        return collect($this->aggregateCategories())->contains(fn (array $category) => $category['slug'] === $slug);
    }

    /**
     * @return array<int, array{slug: string, name: array{ar: string, en: string}}>
     */
    protected function aggregateCategories(): array
    {
        $map = [];

        foreach (Story::query()->published()->pluck('categories') as $categories) {
            if (is_string($categories)) {
                $categories = json_decode($categories, true);
            }

            foreach (is_array($categories) ? $categories : [] as $category) {
                $slug = (string) ($category['slug'] ?? '');
                if ($slug === '' || isset($map[$slug])) {
                    continue;
                }

                $map[$slug] = [
                    'slug' => $slug,
                    'name' => [
                        'ar' => (string) ($category['name_ar'] ?? ''),
                        'en' => (string) ($category['name_en'] ?? ''),
                    ],
                ];
            }
        }

        return array_values($map);
    }

    /**
     * @param  Collection<int, Story>  $featured
     * @return Collection<int, Story>
     */
    protected function mergeLatest(Collection $featured, int $limit): Collection
    {
        $exclude = $featured->pluck('id');

        $latest = Story::query()
            ->published()
            ->when($exclude->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $exclude))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit - $featured->count())
            ->get();

        return $featured->concat($latest)->take($limit)->values();
    }
}
