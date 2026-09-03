<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convert course_trainers.name from plain string to JSON i18n ({ar,en}).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('course_trainers') || ! Schema::hasColumn('course_trainers', 'name')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $rows = DB::table('course_trainers')->select('id', 'name')->get();

        // Rebuild name as JSON via temporary column (works on MySQL / SQLite)
        if (! Schema::hasColumn('course_trainers', 'name_i18n')) {
            Schema::table('course_trainers', function (Blueprint $table) {
                $table->json('name_i18n')->nullable()->after('uuid');
            });
        }

        foreach ($rows as $row) {
            $raw = (string) ($row->name ?? '');
            $decoded = json_decode($raw, true);
            $payload = is_array($decoded)
                ? [
                    'ar' => (string) ($decoded['ar'] ?? $decoded['en'] ?? ''),
                    'en' => (string) ($decoded['en'] ?? $decoded['ar'] ?? ''),
                ]
                : ['ar' => $raw, 'en' => $raw];

            DB::table('course_trainers')->where('id', $row->id)->update([
                'name_i18n' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);
        }

        Schema::table('course_trainers', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        if ($driver === 'sqlite') {
            Schema::table('course_trainers', function (Blueprint $table) {
                $table->json('name')->nullable()->after('uuid');
            });
            foreach (DB::table('course_trainers')->select('id', 'name_i18n')->get() as $row) {
                DB::table('course_trainers')->where('id', $row->id)->update([
                    'name' => $row->name_i18n,
                ]);
            }
            Schema::table('course_trainers', function (Blueprint $table) {
                $table->dropColumn('name_i18n');
            });
        } else {
            Schema::table('course_trainers', function (Blueprint $table) {
                $table->renameColumn('name_i18n', 'name');
            });
        }
    }

    public function down(): void
    {
        // no-op safe rollback omitted
    }
};
