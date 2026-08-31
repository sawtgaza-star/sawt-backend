<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaboration_join_requests', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 5)->nullable()->unique();
            $table->string('type', 80)->index();
            $table->string('company_name');
            $table->string('email');
            $table->string('phone', 40);
            $table->string('country_code', 8)->nullable();
            $table->string('website')->nullable();
            $table->json('payload');
            $table->string('attachment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaboration_join_requests');
    }
};
