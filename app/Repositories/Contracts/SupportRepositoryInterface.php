<?php

namespace App\Repositories\Contracts;

use App\Models\SupportMethod;
use App\Models\SupportPlan;
use App\Models\SupportRequest;
use Illuminate\Database\Eloquent\Collection;

interface SupportRepositoryInterface
{
    /**
     * وسائل الدعم المفعّلة مرتّبة — مع إمكانية الحصر بقسم واحد.
     *
     * @return Collection<int, SupportMethod>
     */
    public function activeMethods(?string $category = null): Collection;

    public function findActiveMethodByUuid(string $uuid): ?SupportMethod;

    public function findActiveMethodById(int $id): ?SupportMethod;

    /**
     * @return Collection<int, SupportPlan>
     */
    public function activePlans(?string $interval = null): Collection;

    public function findActivePlanByUuid(string $uuid): ?SupportPlan;

    public function findRequestByUuid(string $uuid): ?SupportRequest;
}
