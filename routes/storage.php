<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
| Fallback for Hostinger / shared hosting when public/storage symlink is missing
| or broken. Serves files from storage/app/public via Laravel.
*/
Route::get('/storage/{path}', function (string $path) {
    $path = str_replace(['..', '\\'], ['', '/'], $path);

    if ($path === '' || ! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return response()->file(Storage::disk('public')->path($path));
})->where('path', '.*');
