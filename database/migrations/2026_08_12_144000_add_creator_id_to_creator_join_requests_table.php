<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('creator_join_requests', function (Blueprint $table) {
            $table->foreignId('creator_id')->nullable()->after('email')->constrained('creators')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('creator_join_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('creator_id');
        });
    }
};
