<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // اسم الجدول كان "compaigns" بالتصميم الأصلي (خطأ إملائي) — صححناه لـ campaigns
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // مترجم ar/en
            $table->string('slug')->unique();
            $table->json('description')->nullable(); // مترجم ar/en
            $table->string('image')->nullable();
            $table->decimal('target_amount', 10, 2);
            $table->decimal('current_amount', 10, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
