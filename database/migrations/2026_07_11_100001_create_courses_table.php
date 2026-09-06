<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create `courses` (+ join requests) when missing.
 * The original create migration was removed; alter migrations no-op if the
 * table is absent — so fresh/hosting DBs never got courses. This restores it
 * with the full current schema (offline detail + trainer/category FKs).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->nullable()->unique();

                // Prefer FKs when related tables already exist (usual after deploy)
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

                $table->json('title');
                $table->string('slug')->unique();
                $table->json('description')->nullable();
                $table->string('image')->nullable();
                $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
                $table->unsignedInteger('students_count')->default(0);
                $table->enum('status', ['draft', 'published'])->default('draft');
                $table->enum('delivery_mode', ['offline', 'online'])->default('offline');
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
                $table->json('requirements')->nullable();
                $table->json('objectives')->nullable();
                $table->json('modules')->nullable();
                $table->json('outcomes_before')->nullable();
                $table->json('outcomes_after')->nullable();
                $table->json('benefits')->nullable();
                $table->json('selection_steps')->nullable();
                $table->timestamps();
            });
        }

        // Join requests were also skipped when courses was missing
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

    public function down(): void
    {
        Schema::dropIfExists('course_join_requests');
        Schema::dropIfExists('courses');
    }
};
