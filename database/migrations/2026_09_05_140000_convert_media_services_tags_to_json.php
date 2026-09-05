<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Make media_services.tags Spatie-translatable JSON {"ar","en"}
 * (comma-separated string per locale — same UX as before, bilingual).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('media_services', 'tags')) {
            return;
        }

        // Already converted (fresh installs create tags as json with Spatie shape)
        $sample = DB::table('media_services')->value('tags');
        if (is_string($sample) && str_starts_with(ltrim($sample), '{')) {
            $decoded = json_decode($sample, true);
            if (is_array($decoded) && (array_key_exists('ar', $decoded) || array_key_exists('en', $decoded))) {
                return;
            }
        }

        Schema::table('media_services', function (Blueprint $table) {
            $table->json('tags_i18n')->nullable()->after('description');
        });

        foreach (DB::table('media_services')->get() as $row) {
            $plain = is_string($row->tags) ? $row->tags : '';
            // Legacy mixed string → copy into both locales until admin splits them
            DB::table('media_services')->where('id', $row->id)->update([
                'tags_i18n' => json_encode(['ar' => $plain, 'en' => $plain], JSON_UNESCAPED_UNICODE),
            ]);
        }

        Schema::table('media_services', function (Blueprint $table) {
            $table->dropColumn('tags');
        });

        // Avoid renameColumn (needs doctrine/dbal) — add final name then copy + drop temp
        Schema::table('media_services', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('description');
        });

        foreach (DB::table('media_services')->get() as $row) {
            DB::table('media_services')->where('id', $row->id)->update([
                'tags' => $row->tags_i18n,
            ]);
        }

        Schema::table('media_services', function (Blueprint $table) {
            $table->dropColumn('tags_i18n');
        });
    }

    public function down(): void
    {
        // Best-effort: flatten ar (or en) back to a plain text column
        if (! Schema::hasColumn('media_services', 'tags')) {
            return;
        }

        Schema::table('media_services', function (Blueprint $table) {
            $table->text('tags_plain')->nullable()->after('description');
        });

        foreach (DB::table('media_services')->get() as $row) {
            $decoded = is_string($row->tags) ? (json_decode($row->tags, true) ?: []) : [];
            $plain = (string) ($decoded['ar'] ?? $decoded['en'] ?? '');
            DB::table('media_services')->where('id', $row->id)->update(['tags_plain' => $plain]);
        }

        Schema::table('media_services', function (Blueprint $table) {
            $table->dropColumn('tags');
        });

        Schema::table('media_services', function (Blueprint $table) {
            $table->text('tags')->nullable()->after('description');
        });

        foreach (DB::table('media_services')->get() as $row) {
            DB::table('media_services')->where('id', $row->id)->update([
                'tags' => $row->tags_plain,
            ]);
        }

        Schema::table('media_services', function (Blueprint $table) {
            $table->dropColumn('tags_plain');
        });
    }
};
