<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Real calendar date for media works (Filament datepicker).
 * API still returns {ar,en} month/year labels derived from this column.
 * Legacy JSON `date` stays as fallback for older rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('media_works')) {
            return;
        }

        Schema::table('media_works', function (Blueprint $table) {
            if (! Schema::hasColumn('media_works', 'work_date')) {
                $table->date('work_date')->nullable()->after('tag');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('media_works')) {
            return;
        }

        Schema::table('media_works', function (Blueprint $table) {
            if (Schema::hasColumn('media_works', 'work_date')) {
                $table->dropColumn('work_date');
            }
        });
    }
};
