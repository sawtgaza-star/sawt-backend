<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LayoutService;
use Illuminate\Http\JsonResponse;

class LayoutController extends Controller
{
    public function __construct(
        protected LayoutService $layout,
    ) {}

    /**
     * Shared header + footer (Settings → الهيدر / الفوتر).
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->layout->page(),
        ]);
    }

    /**
     * Header only (Settings → الهيدر).
     */
    public function header(): JsonResponse
    {
        return response()->json([
            'data' => $this->layout->header(),
        ]);
    }

    /**
     * Footer only (Settings → الفوتر).
     */
    public function footer(): JsonResponse
    {
        return response()->json([
            'data' => $this->layout->footer(),
        ]);
    }
}
