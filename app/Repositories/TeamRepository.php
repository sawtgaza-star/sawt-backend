<?php

namespace App\Repositories;

use App\Models\Major;
use App\Models\TeamMember;
use App\Repositories\Contracts\TeamRepositoryInterface;
use Illuminate\Support\Collection;

class TeamRepository implements TeamRepositoryInterface
{
    public function activeMajorsWithCounts(): Collection
    {
        return Major::query()
            ->active()
            ->withCount(['members as members_count' => fn ($q) => $q->active()])
            ->orderBy('sort_order')
            ->get();
    }

    public function findActiveMajorBySlug(string $slug): ?Major
    {
        return Major::query()->active()->where('slug', $slug)->first();
    }

    public function findActiveMajorByUuid(string $uuid): ?Major
    {
        return Major::query()->active()->where('uuid', $uuid)->first();
    }

    public function activeMembers(?string $majorSlug = null, ?string $majorUuid = null): Collection
    {
        return TeamMember::query()
            ->active()
            ->with(['major' => fn ($q) => $q->active()])
            ->whereHas('major', fn ($q) => $q->active())
            ->when($majorUuid, fn ($q) => $q->whereHas('major', fn ($m) => $m->where('uuid', $majorUuid)))
            ->when($majorSlug && ! $majorUuid, fn ($q) => $q->whereHas('major', fn ($m) => $m->where('slug', $majorSlug)))
            ->orderBy('sort_order')
            ->get();
    }

    public function findMemberByUuid(string $uuid): ?TeamMember
    {
        return TeamMember::query()
            ->active()
            ->with(['major' => fn ($q) => $q->active()])
            ->where('uuid', $uuid)
            ->first();
    }

    public function relatedMembers(string $excludeUuid, int $limit = 5): Collection
    {
        return TeamMember::query()
            ->active()
            ->with(['major' => fn ($q) => $q->active()])
            ->whereHas('major', fn ($q) => $q->active())
            ->where('uuid', '!=', $excludeUuid)
            ->orderBy('sort_order')
            ->limit(max(1, $limit))
            ->get();
    }
}
