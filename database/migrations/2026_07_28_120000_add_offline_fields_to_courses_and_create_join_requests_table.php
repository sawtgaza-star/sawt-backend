<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Offline course fields + join requests.
 * Base courses table already includes these on fresh installs;
 * this migration only adds missing columns on existing databases.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
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
            if (! Schema::hasColumn('courses', 'max_seats')) {
                $table->unsignedInteger('max_seats')->nullable()->after('ends_at');
            }
            if (! Schema::hasColumn('courses', 'requirements')) {
                $table->json('requirements')->nullable()->after('max_seats');
            }
        });

        if (! Schema::hasTable('course_join_requests')) {
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
    }
};
