<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_socials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('creators')->cascadeOnDelete();
            $table->enum('platform', ['instagram', 'facebook', 'twitter', 'linkedin', 'youtube', 'tiktok', 'telegram', 'other']);
            $table->string('url');
            $table->unsignedBigInteger('followers_count')->default(0);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_socials');
    }
};
