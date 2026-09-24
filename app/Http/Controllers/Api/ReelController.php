<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InstagramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public Instagram reels list (platform account).
 * Collaborators / views / reach need Graph extras (one call per reel).
 * When Settings reels_enabled is off, returns [] with meta.status = disabled (no Graph call).
 */
class ReelController extends Controller
{
    public function index(Request $request, InstagramService $instagram): JsonResponse
    {
        $limit = (int) $request->integer('limit', 12);
        $limit = max(1, min(50, $limit));

        // Default on so collaborators[] is populated (Instagram UI shows co-authors).
        // Pass ?extras=0 for a faster lite list without collaborators/insights.
        $withExtras = ! $request->has('extras')
            || filter_var($request->query('extras'), FILTER_VALIDATE_BOOLEAN);

        return response()->json([
            'data' => $instagram->reels($limit, bypassCache: false, withExtras: $withExtras),
            'meta' => [
                'status' => $instagram->lastStatus(),
                'message' => $instagram->lastMessage(),
                'extras' => $withExtras,
                'enabled' => $instagram->isEnabled(),
            ],
        ]);
    }
}
