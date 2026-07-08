<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_application_socials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_application_id')->constrained('creator_applications')->cascadeOnDelete();
            $table->string('platform');
            $table->string('url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_application_socials');
    }
};
