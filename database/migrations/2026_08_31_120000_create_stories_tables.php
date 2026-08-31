<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 5)->nullable()->unique();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('card_headline')->nullable();
            $table->json('excerpt')->nullable();
            $table->json('card_footer_title')->nullable();
            $table->json('card_footer_subtitle')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('hero_image')->nullable();
            $table->json('content')->nullable();
            $table->json('quote_text')->nullable();
            $table->json('quote_author')->nullable();
            $table->json('categories')->nullable();
            $table->json('author_name')->nullable();
            $table->unsignedSmallInteger('read_time_minutes')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('story_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->string('image');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_images');
        Schema::dropIfExists('stories');
    }
};
