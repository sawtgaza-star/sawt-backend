<?php

namespace App\Support;

/**
 * مصدر واحد لقوائم خيارات وحدة الدعم — تستخدمها موارد Filament والـ API معاً
 * بدل تكرار المصفوفات بكل ملف (زي ما بيصير بموارد التبرعات القديمة).
 */
class SupportOptions
{
    /** أقسام صفحة «اختر طريقة الدعم» الثلاثة */
    public const CATEGORIES = ['electronic', 'transfer', 'crypto'];

    public const INTERVALS = ['one_time', 'monthly', 'yearly'];

    /** ترتيب خطوات الويزارد — يطابق ما يعرضه الفرونت */
    public const STEPS = ['method', 'proof', 'team', 'contact'];

    /**
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            'electronic' => 'دفع إلكتروني',
            'transfer' => 'تحويل مباشر',
            'crypto' => 'عملات رقمية',
        ];
    }

    /**
     * @return array<string, array{ar: string, en: string}>
     */
    public static function categoryLabels(): array
    {
        return [
            'electronic' => ['ar' => 'دفع إلكتروني', 'en' => 'Electronic Payment'],
            'transfer' => ['ar' => 'تحويل مباشر', 'en' => 'Direct Transfer'],
            'crypto' => ['ar' => 'عملات رقمية', 'en' => 'Digital Currencies'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function intervals(): array
    {
        return [
            'one_time' => 'لمرة واحدة',
            'monthly' => 'شهري',
            'yearly' => 'سنوي',
        ];
    }

    /**
     * @return array<string, array{ar: string, en: string}>
     */
    public static function intervalLabels(): array
    {
        return [
            'one_time' => ['ar' => 'لمرة واحدة', 'en' => 'One time'],
            'monthly' => ['ar' => 'شهري', 'en' => 'Monthly'],
            'yearly' => ['ar' => 'سنوي', 'en' => 'Yearly'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function requestStatuses(): array
    {
        return [
            'draft' => __('Incomplete'),
            'pending' => __('Pending review'),
            'under_review' => __('Under review'),
            'approved' => __('Approved'),
            'rejected' => __('Rejected'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function requestStatusColors(): array
    {
        return [
            'draft' => 'gray',
            'pending' => 'warning',
            'under_review' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function subscriptionStatuses(): array
    {
        return [
            'approval_pending' => 'بانتظار الموافقة',
            'active' => 'نشط',
            'suspended' => 'موقوف مؤقتاً',
            'cancelled' => 'ملغى',
            'expired' => 'منتهي',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function subscriptionStatusColors(): array
    {
        return [
            'approval_pending' => 'warning',
            'active' => 'success',
            'suspended' => 'info',
            'cancelled' => 'danger',
            'expired' => 'gray',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function contactPreferences(): array
    {
        return [
            'email' => 'البريد الإلكتروني',
            'whatsapp' => 'واتساب',
            'phone' => 'مكالمة هاتفية',
            'none' => 'لا أرغب بالتواصل',
        ];
    }

    /**
     * @return array<string, array{ar: string, en: string}>
     */
    public static function contactPreferenceLabels(): array
    {
        return [
            'email' => ['ar' => 'البريد الإلكتروني', 'en' => 'Email'],
            'whatsapp' => ['ar' => 'واتساب', 'en' => 'WhatsApp'],
            'phone' => ['ar' => 'مكالمة هاتفية', 'en' => 'Phone call'],
            'none' => ['ar' => 'لا أرغب بالتواصل', 'en' => 'No contact'],
        ];
    }

    /** شبكات العملات الرقمية الشائعة */
    public static function networks(): array
    {
        return [
            'TRC20' => 'TRC20 (Tron)',
            'ERC20' => 'ERC20 (Ethereum)',
            'BEP20' => 'BEP20 (BNB Chain)',
            'SOL' => 'Solana',
            'BTC' => 'Bitcoin',
            'POLYGON' => 'Polygon',
        ];
    }

    /**
     * طريقة الدفع المكافئة بجدول donations عند اعتماد الطلب.
     */
    public static function donationPaymentMethod(string $category, ?string $provider = null): string
    {
        if ($category === 'crypto') {
            return 'crypto';
        }

        if ($category === 'electronic') {
            return $provider === 'paypal' ? 'paypal' : 'card';
        }

        return in_array($provider, ['vodafone_cash', 'instapay', 'revolut_wallet'], true)
            ? 'wallet'
            : 'bank_transfer';
    }
}
