<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per creator↔company collaboration caption (quote/rating/author).
 * Company category for list labels. Video for the section = one shared latest Instagram reel (not per company).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('creator_partner_companies', function (Blueprint $table) {
            // e.g. «فنون وثقافة» under the company name in أبرز التعاونات
            $table->json('category')->nullable()->after('name');
        });

        Schema::table('creator_partner_company_creator', function (Blueprint $table) {
            $table->text('quote_ar')->nullable()->after('sort_order');
            $table->text('quote_en')->nullable()->after('quote_ar');
            $table->unsignedTinyInteger('rating')->default(5)->after('quote_en');
            $table->string('author_name')->nullable()->after('rating');
            $table->string('author_role_ar')->nullable()->after('author_name');
            $table->string('author_role_en')->nullable()->after('author_role_ar');
            $table->string('author_photo')->nullable()->after('author_role_en');
        });
    }

    public function down(): void
    {
        Schema::table('creator_partner_company_creator', function (Blueprint $table) {
            $table->dropColumn([
                'quote_ar',
                'quote_en',
                'rating',
                'author_name',
                'author_role_ar',
                'author_role_en',
                'author_photo',
            ]);
        });

        Schema::table('creator_partner_companies', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
