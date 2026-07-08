<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('username')->unique()->nullable(); // لرابط /creators/{username}
            $table->json('bio')->nullable(); // مترجم ar/en
            $table->string('content_type')->nullable(); // ممثل مسرحي / صحفي...
            $table->unsignedBigInteger('followers_count')->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedInteger('total_videos')->default(0);
            $table->string('avatar')->nullable();
            $table->string('cover')->nullable();
            $table->decimal('monthly_goal_amount', 10, 2)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            // بيانات استلام الدعم (كانت مبعثرة بالتحليل، جمعناها هون منطقياً)
            $table->string('bank_name')->nullable();
            $table->string('bank_account_owner')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_iban')->nullable();
            $table->string('paypal_email')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creators');
    }
};
