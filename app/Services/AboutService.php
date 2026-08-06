<?php

namespace App\Services;

use App\Repositories\Contracts\AboutRepositoryInterface;

class AboutService
{
    public function __construct(
        protected AboutRepositoryInterface $about,
    ) {}

    /**
     * About page payload (no header/footer).
     *
     * @return array<string, mixed>
     */
    public function page(): array
    {
        return $this->about->page();
    }
}
