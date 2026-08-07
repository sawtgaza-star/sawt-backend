<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * وحدة «ادعم صوت» — ثلاثة أقسام (دفع إلكتروني / تحويل مباشر / عملات رقمية)
 * كلها تتدار من لوحة التحكم، مع ويزارد تبرع من أربع خطوات وإثبات تحويل،
 * بالإضافة لاشتراكات PayPal الدورية (شهري / سنوي).
 */
return new class extends Migration
{
    public function up(): void
    {
        // وسائل الدعم — بنوك، محافظ، عملات رقمية… كلها صفوف بهذا الجدول
        Schema::create('support_methods', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->nullable()->unique();

            // القسم الذي تظهر تحته البطاقة بصفحة «اختر طريقة الدعم»
            $table->enum('category', ['electronic', 'transfer', 'crypto'])->index();

            // معرّف تقني ثابت (paypal, vodafone_cash, usdt…) — الفرونت يستخدمه للتفريع
            $table->string('provider')->index();

            $table->json('name');                       // مترجم: {"ar": "فودافون كاش", "en": "Vodafone Cash"}
            $table->json('description')->nullable();    // مترجم
            $table->json('instructions')->nullable();   // مترجم — خطوات التحويل

            $table->string('logo')->nullable();         // شعار الوسيلة
            $table->string('qr_image')->nullable();     // صورة QR للمحفظة (بايننس مثلاً)

            $table->string('account_identifier')->nullable(); // القيمة الأساسية القابلة للنسخ (IBAN / رقم / عنوان محفظة)
            $table->string('account_holder')->nullable();     // اسم صاحب الحساب
            $table->string('network')->nullable();            // شبكة العملة الرقمية: TRC20 / ERC20 / BEP20
            $table->char('currency', 3)->nullable();

            // حقول إضافية حرة: [{label_ar, label_en, value, is_copyable}]
            $table->json('fields')->nullable();

            $table->boolean('requires_proof')->default(true);   // هل يطلب رفع إثبات تحويل
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['category', 'is_active', 'sort_order'], 'support_methods_listing_index');
        });

        // باقات الدعم — لمرة واحدة / شهري / سنوي بمبالغ جاهزة
        Schema::create('support_plans', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->nullable()->unique();

            $table->enum('interval', ['one_time', 'monthly', 'yearly'])->index();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('USD');

            $table->json('label')->nullable();          // مترجم — نص اختياري بدل المبلغ
            $table->json('description')->nullable();     // مترجم

            $table->string('paypal_plan_id')->nullable()->index();  // خطة PayPal للاشتراك الدوري
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });

        // طلب الدعم = جلسة الويزارد كاملة (يُنشأ بالخطوة 1 ويُستكمل خطوة خطوة)
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->nullable()->unique();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('support_method_id')->nullable()->constrained('support_methods')->nullOnDelete();
            $table->foreignId('support_plan_id')->nullable()->constrained('support_plans')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->foreignId('donation_id')->nullable()->constrained('donations')->nullOnDelete();

            $table->enum('category', ['electronic', 'transfer', 'crypto'])->index();
            $table->enum('interval', ['one_time', 'monthly', 'yearly'])->default('one_time');

            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('USD');

            // الخطوة 3 — دعم الفريق
            $table->foreignId('major_id')->nullable()->constrained('majors')->nullOnDelete();
            $table->foreignId('team_member_id')->nullable()->constrained('team_members')->nullOnDelete();
            $table->text('message')->nullable();
            $table->boolean('is_anonymous')->default(false);

            // الخطوة 4 — وسيلة التواصل
            $table->string('donor_name')->nullable();
            $table->string('donor_email')->nullable();
            $table->string('donor_phone')->nullable();
            $table->enum('contact_preference', ['email', 'whatsapp', 'phone', 'none'])->default('email');
            $table->string('contact_value')->nullable();
            $table->boolean('subscribe_newsletter')->default(false);

            // الخطوة 2 — تفاصيل إثبات التحويل
            $table->string('transfer_reference')->nullable();   // رقم العملية / hash
            $table->date('transfer_date')->nullable();
            $table->string('sender_name')->nullable();

            // حالة الويزارد ومراجعة الإدارة
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->enum('status', ['draft', 'pending', 'under_review', 'approved', 'rejected'])
                ->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // لقطات شاشة إثبات التحويل — أكثر من صورة للطلب الواحد
        Schema::create('support_request_proofs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->nullable()->unique();

            $table->foreignId('support_request_id')->constrained('support_requests')->cascadeOnDelete();

            $table->string('path');                     // قرص private — لا يُخدم مباشرة
            $table->string('disk')->default('local');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->timestamps();
        });

        // اشتراكات PayPal الدورية
        Schema::create('support_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->nullable()->unique();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('support_plan_id')->nullable()->constrained('support_plans')->nullOnDelete();

            $table->string('gateway')->default('paypal');
            $table->string('gateway_subscription_id')->nullable()->unique();
            $table->string('gateway_plan_id')->nullable()->index();

            $table->enum('interval', ['monthly', 'yearly'])->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('USD');

            $table->enum('status', ['approval_pending', 'active', 'suspended', 'cancelled', 'expired'])
                ->default('approval_pending')->index();

            $table->string('subscriber_name')->nullable();
            $table->string('subscriber_email')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->unsignedInteger('cycles_completed')->default(0);

            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_subscriptions');
        Schema::dropIfExists('support_request_proofs');
        Schema::dropIfExists('support_requests');
        Schema::dropIfExists('support_plans');
        Schema::dropIfExists('support_methods');
    }
};
