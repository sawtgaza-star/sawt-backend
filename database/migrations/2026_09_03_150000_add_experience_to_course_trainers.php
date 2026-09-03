<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add translatable experience badge (e.g. «7 سنوات») for incubator experts section.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('course_trainers')) {
            return;
        }

        if (! Schema::hasColumn('course_trainers', 'experience')) {
            Schema::table('course_trainers', function (Blueprint $table) {
                $table->json('experience')->nullable()->after('bio');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('course_trainers') && Schema::hasColumn('course_trainers', 'experience')) {
            Schema::table('course_trainers', function (Blueprint $table) {
                $table->dropColumn('experience');
            });
        }
    }
};
