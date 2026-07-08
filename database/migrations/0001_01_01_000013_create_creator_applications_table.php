<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique(); // SAWT-2026-001
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('content_type')->nullable();
            $table->unsignedBigInteger('followers_count')->nullable();
            $table->text('bio')->nullable();
            $table->text('extra_notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'waitlist'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('creator_id')->nullable()->constrained('creators')->nullOnDelete(); // بعد الموافقة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_applications');
    }
};
