<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageFileController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        $path = str_replace(['..', '\\'], ['', '/'], $path);

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('public')->path($path);

        return response()->file($fullPath, [
            'Content-Type' => mime_content_type($fullPath) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
