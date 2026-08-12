<?php

namespace App\Repositories;

use App\Models\SupportMethod;
use App\Models\SupportPlan;
use App\Models\SupportRequest;
use App\Repositories\Contracts\SupportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SupportRepository implements SupportRepositoryInterface
{
    public function activeMethods(?string $category = null): Collection
    {
        return SupportMethod::query()
            ->active()
            ->when($category, fn ($q) => $q->category($category))
            ->ordered()
            ->get();
    }

    public function findActiveMethodByUuid(string $uuid): ?SupportMethod
    {
        return SupportMethod::query()->active()->where('uuid', $uuid)->first();
    }

    public function findActiveMethodById(int $id): ?SupportMethod
    {
        return SupportMethod::query()->active()->whereKey($id)->first();
    }

    public function activePlans(?string $interval = null): Collection
    {
        return SupportPlan::query()
            ->active()
            ->when($interval, fn ($q) => $q->interval($interval))
            ->ordered()
            ->get();
    }

    public function findActivePlanByUuid(string $uuid): ?SupportPlan
    {
        return SupportPlan::query()->active()->where('uuid', $uuid)->first();
    }

    public function findRequestByUuid(string $uuid): ?SupportRequest
    {
        return SupportRequest::query()
            ->with(['method', 'plan', 'proofs', 'major', 'teamMember', 'donation'])
            ->where('uuid', $uuid)
            ->first();
    }
}
