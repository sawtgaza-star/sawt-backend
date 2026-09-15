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
        // Defaults mirrored from https://sawtgaza.com/support/ — admin can override after seed
        $settings = [
            ['key' => 'support_hero_title_ar', 'value' => 'ادعم المنصة التي توصل أصواتهم', 'type' => 'string'],
            ['key' => 'support_hero_title_en', 'value' => 'Support the platform that carries their voices', 'type' => 'string'],
            ['key' => 'support_hero_desc_ar', 'value' => 'كل تبرع يتحوّل إلى قصة تُروى، وصوت يصل إلى العالم من قلب غزة', 'type' => 'text'],
            ['key' => 'support_hero_desc_en', 'value' => 'Every donation becomes a story told — a voice that reaches the world from the heart of Gaza', 'type' => 'text'],
            ['key' => 'support_hero_donors_value', 'value' => '1,247', 'type' => 'string'],
            ['key' => 'support_hero_donors_label_ar', 'value' => 'متبرع هذا الشهر', 'type' => 'string'],
            ['key' => 'support_hero_donors_label_en', 'value' => 'Donors this month', 'type' => 'string'],
            ['key' => 'support_hero_stories_value', 'value' => '340+', 'type' => 'string'],
            ['key' => 'support_hero_stories_label_ar', 'value' => 'قصة وثقت', 'type' => 'string'],
            ['key' => 'support_hero_stories_label_en', 'value' => 'Stories documented', 'type' => 'string'],
            ['key' => 'support_hero_cta_primary_ar', 'value' => 'تبرع الآن', 'type' => 'string'],
            ['key' => 'support_hero_cta_primary_en', 'value' => 'Donate now', 'type' => 'string'],
            ['key' => 'support_hero_cta_secondary_ar', 'value' => 'أين تذهب تبرعاتي؟', 'type' => 'string'],
            ['key' => 'support_hero_cta_secondary_en', 'value' => 'Where do my donations go?', 'type' => 'string'],

            ['key' => 'support_plans_title_ar', 'value' => 'كيف تريد أن تدعم؟', 'type' => 'string'],
            ['key' => 'support_plans_title_en', 'value' => 'How would you like to support?', 'type' => 'string'],
            ['key' => 'support_plans_desc_ar', 'value' => 'قيمنا هي الأساس الذي نبني عليه صوت، وهي ما يقود طريقة عملنا وتطويرنا المستمر', 'type' => 'text'],
            ['key' => 'support_plans_desc_en', 'value' => 'Our values are the foundation of Sawt — they guide how we work and grow', 'type' => 'text'],
            ['key' => 'support_default_interval', 'value' => 'monthly', 'type' => 'string'],
            ['key' => 'support_default_currency', 'value' => 'USD', 'type' => 'string'],
            ['key' => 'support_min_amount', 'value' => '5', 'type' => 'number'],
            ['key' => 'support_max_amount', 'value' => '100000', 'type' => 'number'],
            ['key' => 'support_custom_amount_enabled', 'value' => '1', 'type' => 'boolean'],
            ['key' => 'support_custom_amount_label_ar', 'value' => 'أو أدخل مبلغ', 'type' => 'string'],
            ['key' => 'support_custom_amount_label_en', 'value' => 'Or enter an amount', 'type' => 'string'],

            ['key' => 'support_impact_title_ar', 'value' => 'تبرعك يعني...', 'type' => 'string'],
            ['key' => 'support_impact_title_en', 'value' => 'Your donation means…', 'type' => 'string'],
            ['key' => 'support_invite_label_ar', 'value' => 'قصص إنسانية من غزة', 'type' => 'string'],
            ['key' => 'support_invite_label_en', 'value' => 'Human stories from Gaza', 'type' => 'string'],
            ['key' => 'support_invite_title_ar', 'value' => 'ادعم المنصة التي توصل أصواتهم', 'type' => 'string'],
            ['key' => 'support_invite_title_en', 'value' => 'Support the platform that carries their voices', 'type' => 'string'],
            ['key' => 'support_impact_quote_ar', 'value' => 'كل تبرع يشجع فيه يعني قصة جديدة توصل للناس — قصة ما كانت تُسمع', 'type' => 'text'],
            ['key' => 'support_impact_quote_en', 'value' => 'Every donation you encourage means a new story that reaches people — a story that would not have been heard', 'type' => 'text'],
            ['key' => 'support_impact_quote_author_ar', 'value' => 'فريق صوت', 'type' => 'string'],
            ['key' => 'support_impact_quote_author_en', 'value' => 'Sawt team', 'type' => 'string'],
            ['key' => 'support_impact_quote_location_ar', 'value' => 'غزة، فلسطين', 'type' => 'string'],
            ['key' => 'support_impact_quote_location_en', 'value' => 'Gaza, Palestine', 'type' => 'string'],

            ['key' => 'support_goal_mode', 'value' => 'manual', 'type' => 'string'],
            ['key' => 'support_goal_target', 'value' => '50000', 'type' => 'number'],
            ['key' => 'support_goal_raised', 'value' => '32450', 'type' => 'number'],
            ['key' => 'support_goal_title_ar', 'value' => 'مجتمع الدعم الحي', 'type' => 'string'],
            ['key' => 'support_goal_title_en', 'value' => 'Live support community', 'type' => 'string'],
            ['key' => 'support_goal_subtitle_ar', 'value' => 'قيمنا هي الأساس الذي نبني عليه صوت، وهي ما يقود طريقة عملنا وتطويرنا المستمر', 'type' => 'text'],
            ['key' => 'support_goal_subtitle_en', 'value' => 'Our values are the foundation of Sawt — they guide how we work and grow', 'type' => 'text'],
            ['key' => 'support_goal_message_ar', 'value' => 'نحتاج :amount$ لإتمام هدف الشهر — ساهم الآن', 'type' => 'string'],
            ['key' => 'support_goal_message_en', 'value' => 'We need $:amount to finish this month’s goal — contribute now', 'type' => 'string'],
            ['key' => 'support_goal_cta_ar', 'value' => 'أضف اسمك للقائمة — تبرع الآن', 'type' => 'string'],
            ['key' => 'support_goal_cta_en', 'value' => 'Add your name to the list — donate now', 'type' => 'string'],

            ['key' => 'support_fund_title_ar', 'value' => 'أين تذهب تبرعاتكم؟', 'type' => 'string'],
            ['key' => 'support_fund_title_en', 'value' => 'Where do your donations go?', 'type' => 'string'],
            ['key' => 'support_fund_subtitle_ar', 'value' => 'كل دولار يُستثمر بمسؤولية — نُبلّغكم بكل تفصيلة لأن ثقتكم أمانة', 'type' => 'text'],
            ['key' => 'support_fund_subtitle_en', 'value' => 'Every dollar is invested responsibly — we report every detail because your trust is a responsibility', 'type' => 'text'],
            ['key' => 'support_partners_title_ar', 'value' => 'شركاؤنا في نشر الصوت', 'type' => 'string'],
            ['key' => 'support_partners_title_en', 'value' => 'Our partners in amplifying the voice', 'type' => 'string'],
            ['key' => 'support_partners_subtitle_ar', 'value' => 'شكراً للمؤسسات والشركات التي تؤمن بمهمتنا وتُوصل صوت أهل غزة للعالم', 'type' => 'text'],
            ['key' => 'support_partners_subtitle_en', 'value' => 'Thanks to organizations that believe in our mission and carry Gaza’s voice to the world', 'type' => 'text'],
            ['key' => 'support_partners_cta_title_ar', 'value' => 'الحقيقة تحتاج من يمولها', 'type' => 'string'],
            ['key' => 'support_partners_cta_title_en', 'value' => 'Truth needs those who fund it', 'type' => 'string'],
            ['key' => 'support_partners_cta_body_ar', 'value' => 'شراكات مؤسسية مع صوت — للجهات التي تريد أن يكون دورها في إيصال الحقيقة للعالم. انضم وأبقِ صوت غزة حياً.', 'type' => 'text'],
            ['key' => 'support_partners_cta_body_en', 'value' => 'Institutional partnerships with Sawt — for organizations that want a role in delivering truth to the world. Join us and keep Gaza’s voice alive.', 'type' => 'text'],
            ['key' => 'support_partners_cta_label_ar', 'value' => 'تواصل معنا', 'type' => 'string'],
            ['key' => 'support_partners_cta_label_en', 'value' => 'Contact us', 'type' => 'string'],

            ['key' => 'support_show_sponsor', 'value' => '1', 'type' => 'boolean'],
            ['key' => 'support_stories_limit', 'value' => '4', 'type' => 'number'],
            ['key' => 'support_stories_title_ar', 'value' => 'أصوات لم نقدر على توصيلها', 'type' => 'string'],
            ['key' => 'support_stories_title_en', 'value' => 'Voices we could not deliver', 'type' => 'string'],
            ['key' => 'support_stories_subtitle_ar', 'value' => 'هذه قصص حقيقية من غزة لم تصل للعالم — لأن الموارد نفدت قبل أن نكمل روايتها', 'type' => 'text'],
            ['key' => 'support_stories_subtitle_en', 'value' => 'Real stories from Gaza that never reached the world — resources ran out before we could finish telling them', 'type' => 'text'],
            ['key' => 'support_stories_cta_title_ar', 'value' => 'دعمك يمنع القصة القادمة من الضياع', 'type' => 'string'],
            ['key' => 'support_stories_cta_title_en', 'value' => 'Your support keeps the next story from being lost', 'type' => 'string'],
            ['key' => 'support_stories_cta_body_ar', 'value' => 'تبرعك اليوم يضمن أن الصوت القادم لن يضيع', 'type' => 'text'],
            ['key' => 'support_stories_cta_body_en', 'value' => 'Your donation today ensures the next voice will not be lost', 'type' => 'text'],
            ['key' => 'support_stories_cta_label_ar', 'value' => 'ادعم المنصة الآن', 'type' => 'string'],
            ['key' => 'support_stories_cta_label_en', 'value' => 'Support the platform now', 'type' => 'string'],

            ['key' => 'support_faq_title_ar', 'value' => 'الأسئلة المتكررة', 'type' => 'string'],
            ['key' => 'support_faq_title_en', 'value' => 'Frequently asked questions', 'type' => 'string'],
            ['key' => 'support_faq_cta_title_ar', 'value' => 'لديك سؤال آخر؟', 'type' => 'string'],
            ['key' => 'support_faq_cta_title_en', 'value' => 'Have another question?', 'type' => 'string'],
            ['key' => 'support_faq_cta_body_ar', 'value' => 'فريقنا جاهز للإجابة — سنردّ عليك خلال ساعات', 'type' => 'text'],
            ['key' => 'support_faq_cta_body_en', 'value' => 'Our team is ready — we usually reply within hours', 'type' => 'text'],
            ['key' => 'support_faq_cta_label_ar', 'value' => 'تواصل معنا', 'type' => 'string'],
            ['key' => 'support_faq_cta_label_en', 'value' => 'Contact us', 'type' => 'string'],

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
        ];

        // updateOrCreate يطلق حدث saved فيمسح كاش المفتاح تلقائياً (راجع Setting::booted)
        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'group' => $setting['group'] ?? 'support',
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                ],
            );
        }
    }
}
