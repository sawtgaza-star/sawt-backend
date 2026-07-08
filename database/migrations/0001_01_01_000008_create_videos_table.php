<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('creator_id')->constrained('creators')->cascadeOnDelete();
            $table->json('title'); // مترجم ar/en
            $table->string('slug')->unique();
            $table->json('description')->nullable(); // مترجم ar/en
            $table->string('video_url')->nullable();
            $table->string('audio_url')->nullable();
            $table->string('cover_url')->nullable(); // كانت "videoscol" بالتصميم الأصلي — غامضة الاسم، وضحناها لصورة الغلاف
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedBigInteger('play_count')->default(0);
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('comment_count')->default(0);
            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
