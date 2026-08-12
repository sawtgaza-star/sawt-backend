<?php

namespace App\Repositories\Contracts;

use App\Models\Creator;
use App\Models\CreatorFaq;
use App\Models\CreatorPartnerCompany;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CreatorPageRepositoryInterface
{
    public function activeCreators(?int $limit = null): Collection;

    public function paginateActiveCreators(int $perPage = 10, ?string $search = null): LengthAwarePaginator;

    public function findCreatorByUuid(string $uuid): ?Creator;

    public function activePartnerCompanies(): Collection;

    public function activeFaqs(): Collection;
}
