<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * التبرعات الناتجة عن اعتماد طلب دعم قد تكون عبر محفظة إلكترونية أو عملة رقمية،
 * فنوسّع enum طريقة الدفع بدل الاكتفاء بـ card/bank_transfer/paypal.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `donations` MODIFY `payment_method` ENUM('card','bank_transfer','paypal','wallet','crypto') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE `donations` SET `payment_method` = 'bank_transfer' WHERE `payment_method` IN ('wallet','crypto')");
        DB::statement("ALTER TABLE `donations` MODIFY `payment_method` ENUM('card','bank_transfer','paypal') NOT NULL");
    }
};
