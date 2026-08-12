<?php

namespace App\Http\Controllers;

use App\Models\SupportRequestProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * تنزيل إثبات التحويل من لوحة التحكم فقط — الملفات على القرص الخاص
 * (storage/app/private) ولا تُخدَم عبر رابط عام.
 */
class SupportProofController extends Controller
{
    public function download(Request $request, string $uuid): StreamedResponse
    {
        abort_unless($request->user()?->can('view_any_support::request'), 403);

        $proof = SupportRequestProof::where('uuid', $uuid)->firstOrFail();

        $disk = Storage::disk($proof->disk ?: 'local');

        abort_unless($disk->exists($proof->path), 404);

        return $disk->download($proof->path, $proof->original_name ?: basename($proof->path));
    }
}
