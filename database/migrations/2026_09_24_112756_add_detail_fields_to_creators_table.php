<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creator detail page: optional manual views override + featured collaboration quote block.
 * Content videos come from Instagram reels where this creator is a collaborator.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            // Manual profile «مشاهدة» when Instagram insights are missing; 0 = derive from collab reels
            $table->unsignedBigInteger('views_count')->default(0)->after('followers_count');

            // «أبرز التعاونات» featured quote (AR|EN via Spatie JSON where noted)
            $table->json('featured_collab_quote')->nullable()->after('is_verified');
            $table->string('featured_collab_author_name')->nullable()->after('featured_collab_quote');
            $table->json('featured_collab_author_role')->nullable()->after('featured_collab_author_name');
            $table->json('featured_collab_company')->nullable()->after('featured_collab_author_role');
            // Display string e.g. "200k مشاهدة" — free text so marketing can format it
            $table->string('featured_collab_views_label')->nullable()->after('featured_collab_company');
        });
    }

    public function down(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            $table->dropColumn([
                'views_count',
                'featured_collab_quote',
                'featured_collab_author_name',
                'featured_collab_author_role',
                'featured_collab_company',
                'featured_collab_views_label',
            ]);
        });
    }
};
