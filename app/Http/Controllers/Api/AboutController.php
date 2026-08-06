<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AboutService;
use Illuminate\Http\JsonResponse;

class AboutController extends Controller
{
    public function __construct(
        protected AboutService $about,
    ) {}

    /**
     * About page content from settings (hero → join).
     * Header / footer are not included.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->about->page(),
        ]);
    }
}
