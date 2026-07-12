<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable()->unique();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'active', 'refunded', 'cancelled'])->default('pending');
            $table->decimal('price_paid', 10, 2)->default(0);
            $table->char('currency', 3)->default('USD');
            $table->timestamp('enrolled_at')->nullable(); // يُضبط عند نجاح الدفع
            $table->timestamps();

            $table->unique(['course_id', 'user_id']); // تسجيل واحد لكل مستخدم بكل كورس
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};
