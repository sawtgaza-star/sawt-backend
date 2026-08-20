<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use App\Models\Video;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ContentPageRepositoryInterface
{
    public function activeCategoriesWithCounts(): Collection;

    public function findActiveCategoryBySlug(string $slug): ?Category;

    public function findActiveCategoryByUuid(string $uuid): ?Category;

    /**
     * @return Collection<int, Video>|LengthAwarePaginator
     */
    public function publishedVideos(
        ?string $categorySlug = null,
        ?string $categoryUuid = null,
        string $sort = 'latest',
        ?string $search = null,
        ?int $perPage = null,
        ?int $limit = null,
    ): Collection|LengthAwarePaginator;

    public function mostViewed(int $limit = 6): Collection;
}
