<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();

            // Hero
            $table->string('hero_image')->nullable();
            $table->json('hero_title')->nullable();
            $table->json('hero_description')->nullable();

            // Intro (من نحن)
            $table->string('intro_image')->nullable();
            $table->json('intro_title')->nullable();
            $table->json('intro_body')->nullable();

            // Core values section headings
            $table->json('values_title')->nullable();
            $table->json('values_subtitle')->nullable();

            // Platform (ما الذي يدفعنا...)
            $table->string('platform_image')->nullable();
            $table->json('platform_title')->nullable();
            $table->json('platform_description')->nullable();

            // Story section headings
            $table->json('story_title')->nullable();
            $table->json('story_subtitle')->nullable();

            // Join CTA
            $table->string('join_image')->nullable();
            $table->json('join_title')->nullable();
            $table->json('join_description')->nullable();
            $table->json('join_button_text')->nullable();
            $table->string('join_button_url')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('about_values', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('about_page_id')->constrained('about_pages')->cascadeOnDelete();
            $table->string('icon')->nullable();
            $table->json('title');
            $table->json('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('about_story_cards', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('about_page_id')->constrained('about_pages')->cascadeOnDelete();
            $table->string('icon')->nullable();
            $table->json('title');
            $table->json('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_story_cards');
        Schema::dropIfExists('about_values');
        Schema::dropIfExists('about_pages');
    }
};
