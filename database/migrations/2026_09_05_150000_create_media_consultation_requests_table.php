<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sawt Media «احجز استشارتك» form submissions.
 * Public POST → pending inbox → Filament approve/reject → email applicant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_consultation_requests', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 16)->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 40);
            $table->string('country_code', 8)->nullable();
            // Optional FK — keep request even if service is deleted
            $table->foreignId('media_service_id')
                ->nullable()
                ->constrained('media_services')
                ->nullOnDelete();
            // Snapshot of service title/slug at submit time (for admin + emails)
            $table->string('service_slug')->nullable();
            $table->string('service_title')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_consultation_requests');
    }
};
