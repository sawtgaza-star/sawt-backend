<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create `courses` with the same offline-only columns Filament edits
 * (location, schedule, card meta, objectives, modules, outcomes, benefits, selection).
 * Also creates `course_join_requests` when missing.
 * If an older skinny `courses` table exists, missing columns are added here
 * (replaces extend_courses_for_detail_page).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->nullable()->unique();

                // Trainer / category (course_* tables — not creators)
                if (Schema::hasTable('course_trainers')) {
                    $table->foreignId('trainer_id')->nullable()->constrained('course_trainers')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('trainer_id')->nullable();
                }
                if (Schema::hasTable('course_categories')) {
                    $table->foreignId('course_category_id')->nullable()->constrained('course_categories')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('course_category_id')->nullable();
                }

                // أساسي (Filament)
                $table->json('title');
                $table->string('slug')->unique();
                $table->json('description')->nullable();
                $table->string('image')->nullable(); // incubator card image only
                $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
                $table->unsignedInteger('students_count')->default(0);
                $table->enum('status', ['draft', 'published'])->default('draft');

                // Filament + model force offline; enum kept so existing rows stay compatible
                $table->enum('delivery_mode', ['offline', 'online'])->default('offline');

                // الجدول والمقاعد
                $table->string('location')->nullable();
                $table->string('location_details')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamp('registration_ends_at')->nullable();
                $table->unsignedSmallInteger('duration_weeks')->nullable();
                $table->string('duration_hours')->nullable();
                $table->string('sessions_hours')->nullable();
                $table->decimal('rating', 2, 1)->nullable();
                $table->boolean('is_coming_soon')->default(false);
                $table->unsignedInteger('max_seats')->nullable();

                // التسجيل + تفاصيل الصفحة
                $table->json('requirements')->nullable();
                $table->json('objectives')->nullable();
                $table->json('modules')->nullable();
                $table->json('outcomes_before')->nullable();
                $table->json('outcomes_after')->nullable();
                $table->json('benefits')->nullable();
                $table->json('selection_steps')->nullable();

                $table->timestamps();
            });
        } else {
            $this->ensureOfflineDetailColumns();
        }

        if (! Schema::hasTable('course_join_requests') && Schema::hasTable('courses') && Schema::hasTable('users')) {
            Schema::create('course_join_requests', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->nullable()->unique();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
                $table->string('full_name')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('message')->nullable();
                $table->text('admin_notes')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->unique(['course_id', 'user_id']);
            });
        }
    }

    /**
     * Backfill Filament offline/detail columns on an existing courses table.
     */
    protected function ensureOfflineDetailColumns(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'trainer_id')) {
                $table->unsignedBigInteger('trainer_id')->nullable()->after('uuid');
            }
            if (! Schema::hasColumn('courses', 'course_category_id')) {
                $table->unsignedBigInteger('course_category_id')->nullable()->after('trainer_id');
            }
            if (! Schema::hasColumn('courses', 'delivery_mode')) {
                $table->enum('delivery_mode', ['offline', 'online'])->default('offline')->after('status');
            }
            if (! Schema::hasColumn('courses', 'location')) {
                $table->string('location')->nullable()->after('delivery_mode');
            }
            if (! Schema::hasColumn('courses', 'location_details')) {
                $table->string('location_details')->nullable()->after('location');
            }
            if (! Schema::hasColumn('courses', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('location_details');
            }
            if (! Schema::hasColumn('courses', 'ends_at')) {
                $table->timestamp('ends_at')->nullable()->after('starts_at');
            }
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
            if (! Schema::hasColumn('courses', 'max_seats')) {
                $table->unsignedInteger('max_seats')->nullable()->after('is_coming_soon');
            }
            if (! Schema::hasColumn('courses', 'requirements')) {
                $table->json('requirements')->nullable()->after('max_seats');
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
    }

    public function down(): void
    {
        Schema::dropIfExists('course_join_requests');
        Schema::dropIfExists('courses');
    }
};
