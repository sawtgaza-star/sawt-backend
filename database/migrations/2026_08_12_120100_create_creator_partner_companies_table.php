<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_partner_companies', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 5)->nullable()->unique();
            $table->json('name');
            $table->string('logo')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('creator_partner_company_creator', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator_partner_company_id');
            $table->foreignId('creator_id')->constrained('creators')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('creator_partner_company_id', 'cpc_company_fk')
                ->references('id')
                ->on('creator_partner_companies')
                ->cascadeOnDelete();

            $table->unique(['creator_partner_company_id', 'creator_id'], 'cpc_company_creator_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_partner_company_creator');
        Schema::dropIfExists('creator_partner_companies');
    }
};
