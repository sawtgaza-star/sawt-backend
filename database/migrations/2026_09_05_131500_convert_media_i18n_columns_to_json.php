<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-shot: convert legacy AR|EN columns → Spatie JSON on existing DBs.
 * No-op when create migrations already use JSON (fresh installs).
 */
return new class extends Migration
{
    public function up(): void
    {
        // —— media_services ——
        if (Schema::hasColumn('media_services', 'title_ar')) {
            if (! Schema::hasColumn('media_services', 'title')) {
                Schema::table('media_services', function (Blueprint $table) {
                    $table->json('title')->nullable()->after('number');
                    $table->json('tagline')->nullable()->after('title');
                    $table->json('description')->nullable()->after('tagline');
                    $table->json('includes')->nullable()->after('gallery');
                });
            }

            foreach (DB::table('media_services')->get() as $row) {
                DB::table('media_services')->where('id', $row->id)->update([
                    'title' => json_encode(['ar' => (string) $row->title_ar, 'en' => (string) ($row->title_en ?? '')], JSON_UNESCAPED_UNICODE),
                    'tagline' => json_encode(['ar' => (string) ($row->tagline_ar ?? ''), 'en' => (string) ($row->tagline_en ?? '')], JSON_UNESCAPED_UNICODE),
                    'description' => json_encode(['ar' => (string) ($row->description_ar ?? ''), 'en' => (string) ($row->description_en ?? '')], JSON_UNESCAPED_UNICODE),
                    'includes' => json_encode(['ar' => (string) ($row->includes_ar ?? ''), 'en' => (string) ($row->includes_en ?? '')], JSON_UNESCAPED_UNICODE),
                ]);
            }

            Schema::table('media_services', function (Blueprint $table) {
                $table->dropColumn([
                    'title_ar', 'title_en',
                    'tagline_ar', 'tagline_en',
                    'description_ar', 'description_en',
                    'includes_ar', 'includes_en',
                ]);
            });
        }

        // —— media_works ——
        if (Schema::hasColumn('media_works', 'title_ar')) {
            if (! Schema::hasColumn('media_works', 'title')) {
                Schema::table('media_works', function (Blueprint $table) {
                    $table->json('title')->nullable()->after('slug');
                    $table->json('category')->nullable()->after('title');
                    $table->json('tag')->nullable()->after('category');
                    $table->json('date')->nullable()->after('tag');
                    $table->json('summary')->nullable()->after('date');
                    $table->json('about')->nullable()->after('highlights');
                    $table->json('challenges')->nullable()->after('about');
                    $table->json('solutions')->nullable()->after('challenges');
                    $table->json('client_role')->nullable()->after('client_name');
                    $table->json('client_quote')->nullable()->after('client_role');
                });
            }

            foreach (DB::table('media_works')->get() as $row) {
                DB::table('media_works')->where('id', $row->id)->update([
                    'title' => json_encode(['ar' => (string) $row->title_ar, 'en' => (string) ($row->title_en ?? '')], JSON_UNESCAPED_UNICODE),
                    'category' => json_encode(['ar' => (string) ($row->category_ar ?? ''), 'en' => (string) ($row->category_en ?? '')], JSON_UNESCAPED_UNICODE),
                    'tag' => json_encode(['ar' => (string) ($row->tag_ar ?? ''), 'en' => (string) ($row->tag_en ?? '')], JSON_UNESCAPED_UNICODE),
                    'date' => json_encode(['ar' => (string) ($row->date_ar ?? ''), 'en' => (string) ($row->date_en ?? '')], JSON_UNESCAPED_UNICODE),
                    'summary' => json_encode(['ar' => (string) ($row->summary_ar ?? ''), 'en' => (string) ($row->summary_en ?? '')], JSON_UNESCAPED_UNICODE),
                    'about' => json_encode(['ar' => (string) ($row->about_ar ?? ''), 'en' => (string) ($row->about_en ?? '')], JSON_UNESCAPED_UNICODE),
                    'challenges' => json_encode(['ar' => (string) ($row->challenges_ar ?? ''), 'en' => (string) ($row->challenges_en ?? '')], JSON_UNESCAPED_UNICODE),
                    'solutions' => json_encode(['ar' => (string) ($row->solutions_ar ?? ''), 'en' => (string) ($row->solutions_en ?? '')], JSON_UNESCAPED_UNICODE),
                    'client_role' => json_encode(['ar' => (string) ($row->client_role_ar ?? ''), 'en' => (string) ($row->client_role_en ?? '')], JSON_UNESCAPED_UNICODE),
                    'client_quote' => json_encode(['ar' => (string) ($row->client_quote_ar ?? ''), 'en' => (string) ($row->client_quote_en ?? '')], JSON_UNESCAPED_UNICODE),
                ]);
            }

            Schema::table('media_works', function (Blueprint $table) {
                $table->dropColumn([
                    'title_ar', 'title_en',
                    'category_ar', 'category_en',
                    'tag_ar', 'tag_en',
                    'date_ar', 'date_en',
                    'summary_ar', 'summary_en',
                    'about_ar', 'about_en',
                    'challenges_ar', 'challenges_en',
                    'solutions_ar', 'solutions_en',
                    'client_role_ar', 'client_role_en',
                    'client_quote_ar', 'client_quote_en',
                ]);
            });
        }
    }

    public function down(): void
    {
        // Irreversible for fresh installs that never had AR|EN columns.
        // Existing converted DBs can re-run create + seed if needed.
    }
};
