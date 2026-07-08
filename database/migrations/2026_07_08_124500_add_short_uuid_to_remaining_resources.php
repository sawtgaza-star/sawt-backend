<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    protected array $tables = [
        'users',
        'donations',
        'creator_applications',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (! Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->string('uuid', 5)->nullable()->unique()->after('id');
                });
            }

            $rows = DB::table($table)->whereNull('uuid')->orderBy('id')->get(['id']);

            foreach ($rows as $row) {
                DB::table($table)->where('id', $row->id)->update([
                    'uuid' => $this->uniqueShortCode($table),
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

    protected function uniqueShortCode(string $table, int $length = 5): string
    {
        do {
            $code = Str::lower(Str::random($length));
        } while (DB::table($table)->where('uuid', $code)->exists());

        return $code;
    }
};
