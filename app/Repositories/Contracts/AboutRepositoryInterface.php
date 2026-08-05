<?php

namespace App\Repositories\Contracts;

interface AboutRepositoryInterface
{
    /**
     * About page content from settings (no header/footer).
     *
     * @return array<string, mixed>
     */
    public function page(): array;
}
