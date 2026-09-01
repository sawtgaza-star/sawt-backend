<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('blogs')
            ->where('status', 'published')
            ->whereNull('published_at')
            ->update(['published_at' => now()->startOfHour()]);
    }

    public function down(): void
    {
        // Non-destructive data fix — nothing to revert.
    }
};
