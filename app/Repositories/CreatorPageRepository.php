<?php

namespace App\Repositories;

use App\Models\Creator;
use App\Models\CreatorFaq;
use App\Models\CreatorPartnerCompany;
use App\Repositories\Contracts\CreatorPageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CreatorPageRepository implements CreatorPageRepositoryInterface
{
    public function activeCreators(?int $limit = null): Collection
    {
        return Creator::query()
            ->active()
            ->with(['user', 'socials'])
            ->orderBy('sort_order')
            ->when($limit, fn ($q) => $q->limit(max(1, $limit)))
            ->get();
    }

    public function paginateActiveCreators(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $search = filled($search) ? trim($search) : null;

        return Creator::query()
            ->active()
            ->with(['user', 'socials'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('username', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('sort_order')
            ->paginate(max(1, $perPage));
    }

    public function findCreatorByUuid(string $uuid): ?Creator
    {
        return Creator::query()
            ->active()
            ->with(['user', 'socials'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function activePartnerCompanies(): Collection
    {
        return CreatorPartnerCompany::query()
            ->active()
            ->with(['creators' => fn ($q) => $q->active()->with('user')])
            ->orderBy('sort_order')
            ->get();
    }

    public function activeFaqs(): Collection
    {
        return CreatorFaq::query()
            ->active()
            ->orderBy('sort_order')
            ->get();
    }
}
