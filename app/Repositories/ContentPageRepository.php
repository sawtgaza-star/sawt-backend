<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Video;
use App\Repositories\Contracts\ContentPageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ContentPageRepository implements ContentPageRepositoryInterface
{
    public function activeCategoriesWithCounts(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->withCount(['videos as videos_count' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->get();
    }

    public function findActiveCategoryBySlug(string $slug): ?Category
    {
        return Category::query()->where('is_active', true)->where('slug', $slug)->first();
    }

    public function findActiveCategoryByUuid(string $uuid): ?Category
    {
        return Category::query()->where('is_active', true)->where('uuid', $uuid)->first();
    }

    public function publishedVideos(
        ?string $categorySlug = null,
        ?string $categoryUuid = null,
        string $sort = 'latest',
        ?string $search = null,
        ?int $perPage = null,
        ?int $limit = null,
    ): Collection|LengthAwarePaginator {
        $search = filled($search) ? trim($search) : null;

        $query = Video::query()
            ->published()
            ->with(['category' => fn ($q) => $q->where('is_active', true), 'creator.user'])
            ->when($categoryUuid, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('uuid', $categoryUuid)->where('is_active', true)))
            ->when($categorySlug && ! $categoryUuid, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)->where('is_active', true)))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('title->ar', 'like', "%{$search}%")
                        ->orWhere('title->en', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            });

        if ($sort === 'most_viewed' || $sort === 'views') {
            $query->mostViewed()->orderByDesc('published_at');
        } else {
            $query->orderByDesc('published_at')->orderByDesc('id');
        }

        if ($perPage !== null) {
            return $query->paginate(max(1, min(50, $perPage)));
        }

        if ($limit !== null) {
            return $query->limit(max(1, $limit))->get();
        }

        return $query->get();
    }

    public function mostViewed(int $limit = 6): Collection
    {
        return Video::query()
            ->published()
            ->with(['category' => fn ($q) => $q->where('is_active', true), 'creator.user'])
            ->mostViewed()
            ->limit(max(1, $limit))
            ->get();
    }
}
