<?php

namespace App\Services;

use App\Repositories\Contracts\TeamRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TeamService
{
    public function __construct(
        protected TeamRepositoryInterface $team,
    ) {}

    /**
     * @return array{majors: \Illuminate\Support\Collection, members: \Illuminate\Support\Collection}
     *
     * @throws ModelNotFoundException
     */
    public function page(?string $majorSlug = null, ?string $majorUuid = null): array
    {
        if (filled($majorUuid)) {
            $major = $this->team->findActiveMajorByUuid($majorUuid);

            if (! $major) {
                throw (new ModelNotFoundException)->setModel(\App\Models\Major::class, [$majorUuid]);
            }
        } elseif (filled($majorSlug)) {
            $major = $this->team->findActiveMajorBySlug($majorSlug);

            if (! $major) {
                throw (new ModelNotFoundException)->setModel(\App\Models\Major::class, [$majorSlug]);
            }
        }

        return [
            'majors' => $this->team->activeMajorsWithCounts(),
            'members' => $this->team->activeMembers($majorSlug, $majorUuid),
        ];
    }
}
