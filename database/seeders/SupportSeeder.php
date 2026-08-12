<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\SupportMethod;
use App\Models\SupportPlan;
use Illuminate\Database\Seeder;

/**
 * بيانات أوّلية لوحدة «ادعم صوت»: الوسائل التي تظهر بالتصميم + الباقات + نصوص الصفحة.
 * كل شيء قابل للتعديل من اللوحة بعد التشغيل — هذه نقطة البداية فقط.
 */
class SupportSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedMethods();
        $this->seedPlans();
        $this->seedSettings();
    }

    protected function seedMethods(): void
    {
        $methods = [
            // ===== دفع إلكتروني =====
            [
                'provider' => 'paypal',
                'category' => 'electronic',
                'name' => ['ar' => 'PayPal', 'en' => 'PayPal'],
                'description' => [
                    'ar' => 'ادفع مباشرة ببطاقتك أو حساب PayPal، والتوثيق يتم آلياً.',
                    'en' => 'Pay directly with your card or PayPal account — verified automatically.',
                ],
                'requires_proof' => false,
                'currency' => 'USD',
                'sort_order' => 1,
            ],

            // ===== تحويل مباشر =====
            [
                'provider' => 'vodafone_cash',
                'category' => 'transfer',
                'name' => ['ar' => 'فودافون كاش', 'en' => 'Vodafone Cash'],
                'account_identifier' => '',
                'account_holder' => '',
                'instructions' => [
                    'ar' => "١. افتح تطبيق فودافون كاش\n٢. اختر «تحويل أموال»\n٣. أدخل الرقم الظاهر أعلاه\n٤. أرسل المبلغ ثم ارفع لقطة شاشة العملية",
                    'en' => "1. Open the Vodafone Cash app\n2. Choose \"Send money\"\n3. Enter the number shown above\n4. Send the amount, then upload a screenshot",
                ],
                'fields' => [
                    ['label_ar' => 'رقم المحفظة', 'label_en' => 'Wallet number', 'value' => '', 'is_copyable' => true],
                ],
                'sort_order' => 2,
            ],
            [
                'provider' => 'instapay',
                'category' => 'transfer',
                'name' => ['ar' => 'انستا باي', 'en' => 'InstaPay'],
                'fields' => [
                    ['label_ar' => 'عنوان الدفع', 'label_en' => 'Payment address', 'value' => '', 'is_copyable' => true],
                ],
                'sort_order' => 3,
            ],
            [
                'provider' => 'cliq',
                'category' => 'transfer',
                'name' => ['ar' => 'حساب كليك', 'en' => 'CliQ Account'],
                'fields' => [
                    ['label_ar' => 'اسم كليك', 'label_en' => 'CliQ alias', 'value' => '', 'is_copyable' => true],
                    ['label_ar' => 'البنك', 'label_en' => 'Bank', 'value' => '', 'is_copyable' => false],
                ],
                'sort_order' => 4,
            ],
            [
                'provider' => 'revolut_wallet',
                'category' => 'transfer',
                'name' => ['ar' => 'محفظة ريفولت', 'en' => 'Revolut Wallet'],
                'fields' => [
                    ['label_ar' => 'اسم المستخدم', 'label_en' => 'Revtag', 'value' => '', 'is_copyable' => true],
                ],
                'sort_order' => 5,
            ],
            [
                'provider' => 'revolut_bank',
                'category' => 'transfer',
                'name' => ['ar' => 'حساب ريفولت - حساب بنكي أوروبي', 'en' => 'Revolut — European Bank Account'],
                'currency' => 'EUR',
                'fields' => [
                    ['label_ar' => 'اسم صاحب الحساب', 'label_en' => 'Account holder', 'value' => '', 'is_copyable' => false],
                    ['label_ar' => 'IBAN', 'label_en' => 'IBAN', 'value' => '', 'is_copyable' => true],
                    ['label_ar' => 'BIC / SWIFT', 'label_en' => 'BIC / SWIFT', 'value' => '', 'is_copyable' => true],
                ],
                'sort_order' => 6,
            ],
            [
                'provider' => 'bank_alyusr',
                'category' => 'transfer',
                'name' => ['ar' => 'بنك اليسر', 'en' => 'Al-Yusr Bank'],
                'fields' => [
                    ['label_ar' => 'اسم البنك', 'label_en' => 'Bank name', 'value' => 'بنك اليسر', 'is_copyable' => false],
                    ['label_ar' => 'اسم صاحب الحساب', 'label_en' => 'Account holder', 'value' => '', 'is_copyable' => false],
                    ['label_ar' => 'رقم الحساب', 'label_en' => 'Account number', 'value' => '', 'is_copyable' => true],
                    ['label_ar' => 'IBAN', 'label_en' => 'IBAN', 'value' => '', 'is_copyable' => true],
                ],
                'sort_order' => 7,
            ],

            // ===== عملات رقمية =====
            [
                'provider' => 'usdt',
                'category' => 'crypto',
                'name' => ['ar' => 'عملة رقمية USDT', 'en' => 'USDT'],
                'network' => 'TRC20',
                'currency' => 'USD',
                'instructions' => [
                    'ar' => "١. امسح رمز QR أو انسخ عنوان المحفظة\n٢. تأكد أن الشبكة المختارة مطابقة تماماً\n٣. أرسل المبلغ ثم ارفع لقطة شاشة العملية مع رقم الـ Hash",
                    'en' => "1. Scan the QR code or copy the wallet address\n2. Make sure the network matches exactly\n3. Send the amount, then upload a screenshot with the transaction hash",
                ],
                'fields' => [
                    ['label_ar' => 'عنوان المحفظة', 'label_en' => 'Wallet address', 'value' => '', 'is_copyable' => true],
                    ['label_ar' => 'الشبكة', 'label_en' => 'Network', 'value' => 'TRC20', 'is_copyable' => false],
                ],
                'sort_order' => 8,
            ],
            [
                'provider' => 'usdc',
                'category' => 'crypto',
                'name' => ['ar' => 'عملة رقمية USDC', 'en' => 'USDC'],
                'network' => 'ERC20',
                'currency' => 'USD',
                'fields' => [
                    ['label_ar' => 'عنوان المحفظة', 'label_en' => 'Wallet address', 'value' => '', 'is_copyable' => true],
                    ['label_ar' => 'الشبكة', 'label_en' => 'Network', 'value' => 'ERC20', 'is_copyable' => false],
                ],
                'sort_order' => 9,
            ],
        ];

        foreach ($methods as $method) {
            SupportMethod::updateOrCreate(
                ['provider' => $method['provider']],
                $method + ['is_active' => true],
            );
        }
    }

    protected function seedPlans(): void
    {
        $amounts = [50, 100, 150, 250];

        foreach (['one_time', 'monthly', 'yearly'] as $interval) {
            foreach ($amounts as $index => $amount) {
                SupportPlan::updateOrCreate(
                    ['interval' => $interval, 'amount' => $amount, 'currency' => 'USD'],
                    [
                        'is_featured' => $amount === 150,
                        'is_active' => true,
                        'sort_order' => $index,
                    ],
                );
            }
        }
    }

    protected function seedSettings(): void
    {
        $settings = [
            ['key' => 'support_hero_title_ar', 'value' => 'ادعم صوت', 'type' => 'string'],
            ['key' => 'support_hero_title_en', 'value' => 'Support Sawt', 'type' => 'string'],
            ['key' => 'support_hero_desc_ar', 'value' => 'اختر الطريقة الأنسب لك لإتمام تبرعك، وكل مساهمة تتحول إلى قصة تُروى من قلب غزة', 'type' => 'text'],
            ['key' => 'support_hero_desc_en', 'value' => 'Choose the way that suits you best — every contribution becomes a story told from the heart of Gaza', 'type' => 'text'],
            ['key' => 'support_methods_title_ar', 'value' => 'اختر طريقة الدعم التي تناسبك', 'type' => 'string'],
            ['key' => 'support_methods_title_en', 'value' => 'Choose the support method that suits you', 'type' => 'string'],

            ['key' => 'support_cat_electronic_title_ar', 'value' => 'دفع إلكتروني', 'type' => 'string'],
            ['key' => 'support_cat_electronic_title_en', 'value' => 'Electronic Payment', 'type' => 'string'],
            ['key' => 'support_cat_electronic_desc_ar', 'value' => 'يتم التبرع باستخدام بوابة دفع آمنة وسهلة الاستخدام، بحيث يقدر المتبرع إتمام العملية بسرعة وبطريقة موثوقة.', 'type' => 'text'],
            ['key' => 'support_cat_electronic_desc_en', 'value' => 'Donate through a secure, easy-to-use payment gateway and complete the process quickly and reliably.', 'type' => 'text'],
            ['key' => 'support_cat_electronic_accent', 'value' => '#F97316', 'type' => 'string'],
            ['key' => 'support_cat_electronic_enabled', 'value' => '1', 'type' => 'boolean'],

            ['key' => 'support_cat_transfer_title_ar', 'value' => 'تحويل مباشر', 'type' => 'string'],
            ['key' => 'support_cat_transfer_title_en', 'value' => 'Direct Transfer', 'type' => 'string'],
            ['key' => 'support_cat_transfer_desc_ar', 'value' => 'يتم التبرع من خلال بيانات حساب بنكي أو محفظة إلكترونية، ثم يقوم المتبرع بإرفاق إثبات التحويل ليتم توثيق التبرع.', 'type' => 'text'],
            ['key' => 'support_cat_transfer_desc_en', 'value' => 'Donate via a bank account or e-wallet, then attach the transfer proof so we can verify your donation.', 'type' => 'text'],
            ['key' => 'support_cat_transfer_accent', 'value' => '#4D6B2F', 'type' => 'string'],
            ['key' => 'support_cat_transfer_enabled', 'value' => '1', 'type' => 'boolean'],

            ['key' => 'support_cat_crypto_title_ar', 'value' => 'عملات رقمية', 'type' => 'string'],
            ['key' => 'support_cat_crypto_title_en', 'value' => 'Digital Currencies', 'type' => 'string'],
            ['key' => 'support_cat_crypto_desc_ar', 'value' => 'يتم التبرع باستخدام عملات رقمية مدعومة، مع إمكانية إرسال إثبات العملية بعد التحويل لتأكيد المساهمة.', 'type' => 'text'],
            ['key' => 'support_cat_crypto_desc_en', 'value' => 'Donate using supported digital currencies and send the transaction proof afterwards to confirm your contribution.', 'type' => 'text'],
            ['key' => 'support_cat_crypto_accent', 'value' => '#4B5563', 'type' => 'string'],
            ['key' => 'support_cat_crypto_enabled', 'value' => '1', 'type' => 'boolean'],

            ['key' => 'support_step_method_label_ar', 'value' => 'اختيار المنصة', 'type' => 'string'],
            ['key' => 'support_step_method_label_en', 'value' => 'Choose platform', 'type' => 'string'],
            ['key' => 'support_step_proof_label_ar', 'value' => 'إثبات التبرع', 'type' => 'string'],
            ['key' => 'support_step_proof_label_en', 'value' => 'Donation proof', 'type' => 'string'],
            ['key' => 'support_step_team_label_ar', 'value' => 'دعم الفريق', 'type' => 'string'],
            ['key' => 'support_step_team_label_en', 'value' => 'Support the team', 'type' => 'string'],
            ['key' => 'support_step_contact_label_ar', 'value' => 'وسيلة التواصل', 'type' => 'string'],
            ['key' => 'support_step_contact_label_en', 'value' => 'Contact method', 'type' => 'string'],

            ['key' => 'support_plans_title_ar', 'value' => 'كيف تريد أن تدعم؟', 'type' => 'string'],
            ['key' => 'support_plans_title_en', 'value' => 'How would you like to support?', 'type' => 'string'],
            ['key' => 'support_default_interval', 'value' => 'monthly', 'type' => 'string'],
            ['key' => 'support_default_currency', 'value' => 'USD', 'type' => 'string'],
            ['key' => 'support_min_amount', 'value' => '5', 'type' => 'number'],
            ['key' => 'support_max_amount', 'value' => '100000', 'type' => 'number'],
            ['key' => 'support_custom_amount_enabled', 'value' => '1', 'type' => 'boolean'],
        ];

        // updateOrCreate يطلق حدث saved فيمسح كاش المفتاح تلقائياً (راجع Setting::booted)
        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'group' => 'support',
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                ],
            );
        }
    }
}
