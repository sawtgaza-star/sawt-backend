<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sawt Media portfolio works — detail page /media/works/{slug}.
 * Translatable fields as Spatie JSON; nested repeaters (stages, highlights, results)
 * keep title_ar/title_en keys inside the JSON arrays (not DB columns).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_works', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 16)->unique();
            $table->foreignId('media_service_id')
                ->nullable()
                ->constrained('media_services')
                ->nullOnDelete();
            $table->string('slug')->unique();
            // Spatie HasTranslations — {"ar":"…","en":"…"}
            $table->json('title');
            $table->json('category')->nullable();
            $table->json('tag')->nullable();
            $table->json('date')->nullable();
            $table->json('summary')->nullable();
            $table->string('cover_image')->nullable();
            // Top highlight stats (+60%, +590 M) — [{value, label_ar, label_en}]
            $table->json('highlights')->nullable();
            // Tab: عن المشروع
            $table->json('about')->nullable();
            $table->json('challenges')->nullable();
            $table->json('solutions')->nullable();
            // Tab: المراحل — [{title_ar, title_en, body_ar, body_en}]
            $table->json('stages')->nullable();
            // Tab: رأي العميل
            $table->string('client_name')->nullable();
            $table->json('client_role')->nullable();
            $table->json('client_quote')->nullable();
            $table->string('client_avatar')->nullable();
            // Results section stats — [{value, label_ar, label_en}]
            $table->json('results')->nullable();
            // صور من المشروع
            $table->json('gallery')->nullable();
            $table->boolean('show_on_landing')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_works');
    }
};
