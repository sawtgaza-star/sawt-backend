<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guest course "Subscribe now" applications (3-step modal).
 * Separate from authenticated course_join_requests (join / waitlist).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop unused stub that targeted join requests — subscribe is its own table
        Schema::create('course_subscribe_requests', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique()->nullable();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();

            // Step 1 — personal (all required at API validation)
            $table->string('full_name')->required();
            $table->string('phone', 40)->required();
            $table->string('phone_country_code', 10)->nullable();
            $table->string('email')->required();

            // Step 2 — academic / professional (first two required)
            $table->string('academic_level')->required();
            $table->boolean('attended_similar_course')->required();
            $table->text('goals_interests')->nullable();

            // Step 3 — goals (join_goal required; notes optional)
            $table->string('join_goal')->required();
            $table->string('additional_notes', 500)->nullable();

            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // One active application per email per course (rejected can resubmit)
            $table->index(['course_id', 'email']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_subscribe_requests');
    }
};
