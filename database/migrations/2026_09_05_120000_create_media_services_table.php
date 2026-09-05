<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sawt Media services as their own table (not settings JSON).
 * Translatable fields (title, tagline, …) stored as Spatie JSON {"ar","en"}.
 * Detail page: GET /api/v1/pages/media/services/{slug}
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_services', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 16)->unique();
            $table->string('slug')->unique();
            $table->string('number', 10)->nullable();
            // Spatie HasTranslations — {"ar":"…","en":"…"}
            $table->json('title');
            $table->json('tagline')->nullable();
            $table->json('description')->nullable();
            // Spatie JSON — comma-separated tags per locale {"ar":"…","en":"…"}
            $table->json('tags')->nullable();
            $table->string('image')->nullable();
            // Multiline bullets for «ماذا تشمل الخدمة»
            $table->json('includes')->nullable();
            // Sample works for the service detail page (legacy; prefer media_works FK)
            $table->json('samples')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_services');
    }
};
