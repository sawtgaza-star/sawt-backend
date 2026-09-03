<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create course_trainers + course_categories and point courses at them
 * (drops instructor_id / category_id links to creators/content categories).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_trainers', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name');
            $table->json('title')->nullable(); // role / specialty AR+EN
            $table->json('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('socials')->nullable(); // [{platform, url}]
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->json('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (! Schema::hasColumn('courses', 'trainer_id')) {
                    $table->foreignId('trainer_id')->nullable()->after('uuid')->constrained('course_trainers')->nullOnDelete();
                }
                if (! Schema::hasColumn('courses', 'course_category_id')) {
                    $table->foreignId('course_category_id')->nullable()->after('trainer_id')->constrained('course_categories')->nullOnDelete();
                }
            });

            // Detach from creators / content categories
            $this->dropOldCourseForeignKeys();

            Schema::table('courses', function (Blueprint $table) {
                if (Schema::hasColumn('courses', 'instructor_id')) {
                    $table->dropColumn('instructor_id');
                }
                if (Schema::hasColumn('courses', 'category_id')) {
                    $table->dropColumn('category_id');
                }
            });
        }
    }

    protected function dropOldCourseForeignKeys(): void
    {
        foreach (['instructor_id', 'category_id'] as $column) {
            if (! Schema::hasColumn('courses', $column)) {
                continue;
            }

            try {
                Schema::table('courses', function (Blueprint $table) use ($column) {
                    $table->dropForeign([$column]);
                });
            } catch (\Throwable) {
                // Column may not have a named FK on some installs.
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (Schema::hasColumn('courses', 'trainer_id')) {
                    $table->dropConstrainedForeignId('trainer_id');
                }
                if (Schema::hasColumn('courses', 'course_category_id')) {
                    $table->dropConstrainedForeignId('course_category_id');
                }
                if (! Schema::hasColumn('courses', 'instructor_id')) {
                    $table->foreignId('instructor_id')->nullable()->after('uuid');
                }
                if (! Schema::hasColumn('courses', 'category_id')) {
                    $table->foreignId('category_id')->nullable()->after('instructor_id');
                }
            });
        }

        Schema::dropIfExists('course_categories');
        Schema::dropIfExists('course_trainers');
    }
};
