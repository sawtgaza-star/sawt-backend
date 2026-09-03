<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Full course detail fields (match incubator course landing) + force offline.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'registration_ends_at')) {
                $table->timestamp('registration_ends_at')->nullable()->after('ends_at');
            }
            if (! Schema::hasColumn('courses', 'duration_weeks')) {
                $table->unsignedSmallInteger('duration_weeks')->nullable()->after('registration_ends_at');
            }
            if (! Schema::hasColumn('courses', 'duration_hours')) {
                $table->string('duration_hours')->nullable()->after('duration_weeks');
            }
            if (! Schema::hasColumn('courses', 'sessions_hours')) {
                $table->string('sessions_hours')->nullable()->after('duration_hours');
            }
            if (! Schema::hasColumn('courses', 'rating')) {
                $table->decimal('rating', 2, 1)->nullable()->after('sessions_hours');
            }
            if (! Schema::hasColumn('courses', 'is_coming_soon')) {
                $table->boolean('is_coming_soon')->default(false)->after('rating');
            }
            if (! Schema::hasColumn('courses', 'objectives')) {
                $table->json('objectives')->nullable()->after('requirements');
            }
            if (! Schema::hasColumn('courses', 'modules')) {
                $table->json('modules')->nullable()->after('objectives');
            }
            if (! Schema::hasColumn('courses', 'outcomes_before')) {
                $table->json('outcomes_before')->nullable()->after('modules');
            }
            if (! Schema::hasColumn('courses', 'outcomes_after')) {
                $table->json('outcomes_after')->nullable()->after('outcomes_before');
            }
            if (! Schema::hasColumn('courses', 'benefits')) {
                $table->json('benefits')->nullable()->after('outcomes_after');
            }
            if (! Schema::hasColumn('courses', 'selection_steps')) {
                $table->json('selection_steps')->nullable()->after('benefits');
            }
        });

        // All courses are offline.
        if (Schema::hasColumn('courses', 'delivery_mode')) {
            DB::table('courses')->update(['delivery_mode' => 'offline']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            foreach ([
                'registration_ends_at',
                'duration_weeks',
                'duration_hours',
                'sessions_hours',
                'rating',
                'is_coming_soon',
                'objectives',
                'modules',
                'outcomes_before',
                'outcomes_after',
                'benefits',
                'selection_steps',
            ] as $column) {
                if (Schema::hasColumn('courses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
