<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InstagramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReelController extends Controller
{
    public function index(Request $request, InstagramService $instagram): JsonResponse
    {
        $limit = (int) $request->integer('limit', 12);
        $limit = max(1, min($limit, 50));

        return response()->json([
            'data' => $instagram->reels($limit),
            'meta' => [
                'status' => $instagram->lastStatus(),
                'message' => $instagram->lastMessage(),
            ],
        ]);
    }
}
