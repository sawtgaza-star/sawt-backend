<?php

namespace App\Repositories\Contracts;

use App\Models\Major;
use App\Models\TeamMember;
use Illuminate\Support\Collection;

interface TeamRepositoryInterface
{
    public function activeMajorsWithCounts(): Collection;

    public function findActiveMajorBySlug(string $slug): ?Major;

    public function findActiveMajorByUuid(string $uuid): ?Major;

    public function activeMembers(?string $majorSlug = null, ?string $majorUuid = null): Collection;

    public function findMemberByUuid(string $uuid): ?TeamMember;

    /**
     * Other active members for the detail page grid (excludes current).
     */
    public function relatedMembers(string $excludeUuid, int $limit = 5): Collection;
}
