<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_collaborations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('creators')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('company_logo')->nullable();
            $table->json('description')->nullable(); // مترجم ar/en
            $table->string('reviewer_name')->nullable();
            $table->string('reviewer_role')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('featured_video_url')->nullable();
            $table->unsignedInteger('featured_video_views')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_collaborations');
    }
};
