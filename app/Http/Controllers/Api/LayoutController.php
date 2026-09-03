<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LayoutService;
use Illuminate\Http\JsonResponse;

/**
 * Public layout endpoints for main site + incubator chrome.
 * Settings: الإعدادات العامة / إعدادات الحاضنة.
 */
class LayoutController extends Controller
{
    public function __construct(
        protected LayoutService $layout,
    ) {}

    /**
     * Shared navbar + footer (Settings → الهيدر / الفوتر).
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->layout->page(),
        ]);
    }

    /**
     * Navbar only (Settings → الهيدر).
     */
    public function navbar(): JsonResponse
    {
        return response()->json([
            'data' => $this->layout->navbar(),
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

    /**
     * Incubator navbar + footer (الإعدادات → إعدادات الحاضنة).
     */
    public function incubator(): JsonResponse
    {
        return response()->json([
            'data' => $this->layout->incubatorPage(),
        ]);
    }

    /**
     * Incubator navbar only.
     */
    public function incubatorNavbar(): JsonResponse
    {
        return response()->json([
            'data' => $this->layout->incubatorNavbar(),
        ]);
    }

    /**
     * Incubator footer only.
     */
    public function incubatorFooter(): JsonResponse
    {
        return response()->json([
            'data' => $this->layout->incubatorFooter(),
        ]);
    }
}
