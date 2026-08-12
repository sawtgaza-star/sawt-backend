<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_join_requests', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 5)->nullable()->unique();
            $table->string('full_name');
            $table->string('phone', 40);
            $table->string('country_code', 8)->nullable();
            $table->string('email');
            $table->json('content_types')->nullable();
            $table->unsignedBigInteger('followers_count')->default(0);
            $table->text('content_bio')->nullable();
            $table->json('socials')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_join_requests');
    }
};
