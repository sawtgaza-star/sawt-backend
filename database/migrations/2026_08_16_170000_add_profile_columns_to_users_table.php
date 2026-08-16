<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restore profile columns expected by the app on users
 * (phone, country_code, avatar, status). Safe for production DBs
 * that only have the base Laravel users columns + type/uuid.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 40)->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'country_code')) {
                $after = Schema::hasColumn('users', 'phone') ? 'phone' : 'email';
                $table->string('country_code', 8)->nullable()->default('+970')->after($after);
            }

            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'status')) {
                $after = Schema::hasColumn('users', 'avatar') ? 'avatar' : 'password';
                $table->enum('status', ['active', 'inactive', 'banned'])
                    ->default('active')
                    ->after($after);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            foreach (['phone', 'country_code', 'avatar', 'status'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
