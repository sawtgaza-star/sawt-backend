<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول دفعات موحّد (polymorphic) — يخدم التبرعات والكورسات معاً.
     * payable = Donation أو CourseEnrollment.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable()->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->morphs('payable'); // payable_type + payable_id (+ index)
            $table->string('gateway')->default('paypal');
            $table->string('gateway_order_id')->nullable()->index();   // PayPal order id
            $table->string('gateway_capture_id')->nullable()->unique(); // PayPal capture id
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('payer_email')->nullable();
            $table->string('payer_name')->nullable();
            $table->json('meta')->nullable(); // استجابة PayPal الخام
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
