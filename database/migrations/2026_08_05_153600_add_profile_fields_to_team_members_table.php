<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->unsignedInteger('years_of_experience')->nullable()->after('role');
            $table->json('bio')->nullable()->after('years_of_experience');
            $table->string('facebook_url')->nullable()->after('photo');
            $table->string('linkedin_url')->nullable()->after('facebook_url');
            $table->string('twitter_url')->nullable()->after('linkedin_url');
            $table->string('instagram_url')->nullable()->after('twitter_url');
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn([
                'years_of_experience',
                'bio',
                'facebook_url',
                'linkedin_url',
                'twitter_url',
                'instagram_url',
            ]);
        });
    }
};
