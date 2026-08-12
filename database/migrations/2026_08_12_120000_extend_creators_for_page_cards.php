<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            $table->json('role')->nullable()->after('bio');
            $table->unsignedInteger('sort_order')->default(0)->after('status');
            $table->boolean('is_verified')->default(false)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            $table->dropColumn(['role', 'sort_order', 'is_verified']);
        });
    }
};
