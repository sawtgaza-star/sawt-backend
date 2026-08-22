<?php

namespace App\Services;

use App\Models\Blog;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\MediaUrl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class BlogService
{
    public function __construct(
        protected SettingRepositoryInterface $settings,
    ) {}

    /**
     * Homepage news block (section chrome + latest blog cards).
     *
     * @return array<string, mixed>
     */
    public function homepageNews(): array
    {
        $limit = (int) ($this->settings->get('home_news_limit', 3) ?: 3);
        $limit = max(1, min(12, $limit));

        $featured = Blog::query()
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
            'title' => $this->settings->i18n('home_news_title', 'آخر أخبارنا', 'Our Latest News'),
            'subtitle' => $this->settings->i18n(
                'home_news_subtitle',
                'شاهد أحدث القصص والفيديوهات من منصة صوت',
                'Watch the latest stories and videos from Sawt'
            ),
            'read_more' => $this->settings->i18n('home_news_read_more', 'اقرأ المزيد', 'Read more'),
            'view_all' => [
                'label' => $this->settings->i18n('home_news_view_all', 'عرض جميع الأخبار', 'View all news'),
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
            throw (new ModelNotFoundException)->setModel(Blog::class, [$categorySlug]);
        }

        $query = Blog::query()
            ->published()
            ->when(filled($categorySlug), fn ($q) => $q->withCategorySlug((string) $categorySlug))
            ->when(filled($search), function ($q) use ($search) {
                $term = trim((string) $search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('title->ar', 'like', "%{$term}%")
                        ->orWhere('title->en', 'like', "%{$term}%")
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
        $blog = Blog::query()
            ->published()
            ->with('images')
            ->where('uuid', $uuid)
            ->firstOrFail();

        $blog->increment('views_count');

        $slugs = $blog->categorySlugs();

        $related = Blog::query()
            ->published()
            ->with('images')
            ->where('uuid', '!=', $blog->uuid)
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
            'blog' => $blog,
            'related' => $related,
        ];
    }

    /**
     * Page hero from Settings (listing + detail).
     *
     * @return array<string, mixed>
     */
    protected function hero(): array
    {
        return [
            'image_url' => MediaUrl::make($this->settings->get('blog_header_bg')),
            'title' => $this->settings->i18n('blog_hero_title', 'آخر الأخبار', 'Latest News'),
            'description' => $this->settings->i18n(
                'blog_hero_desc',
                'تابع أحدث قصص وتحديثات منصة صوت',
                'Follow the latest stories and updates from Sawt'
            ),
        ];
    }

    /**
     * @return array<int, array{slug: string, name: array{ar: string, en: string}}>
     */
    protected function aggregateCategories(): array
    {
        $map = [];

        foreach (Blog::query()->published()->pluck('categories') as $categories) {
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

    protected function categoryExists(string $slug): bool
    {
        return collect($this->aggregateCategories())->contains(fn (array $category) => $category['slug'] === $slug);
    }

    /**
     * @param  Collection<int, Blog>  $featured
     * @return Collection<int, Blog>
     */
    protected function mergeLatest(Collection $featured, int $limit): Collection
    {
        $exclude = $featured->pluck('id');

        $latest = Blog::query()
            ->published()
            ->when($exclude->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $exclude))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit - $featured->count())
            ->get();

        return $featured->concat($latest)->take($limit)->values();
    }
}
