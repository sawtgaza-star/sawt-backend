<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    protected array $tables = [
        'campaigns',
        'categories',
        'tags',
        'videos',
        'creators',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (! Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->uuid('uuid')->nullable()->unique()->after('id');
                });
            }

            $rows = DB::table($table)->whereNull('uuid')->orderBy('id')->get(['id']);

            foreach ($rows as $row) {
                DB::table($table)->where('id', $row->id)->update([
                    'uuid' => (string) Str::uuid(),
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropUnique(['uuid']);
                    $blueprint->dropColumn('uuid');
                });
            }
        }
    }
};
