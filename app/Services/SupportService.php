<?php

namespace App\Services;

use App\Http\Resources\SupportMethodResource;
use App\Models\SupportMethod;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\SupportRepositoryInterface;
use App\Support\MediaUrl;
use App\Support\SupportOptions;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * محتوى صفحات «ادعم صوت»: الهيرو والأقسام الثلاثة والوسائل والباقات وتعريف الويزارد.
 * النصوص الثابتة من جدول settings (نفس نمط صفحتَي «من نحن» و«الفريق»)،
 * والوسائل/الباقات من جداولها الخاصة عشان تتدار كـ CRUD من اللوحة.
 */
class SupportService
{
    public function __construct(
        protected SupportRepositoryInterface $support,
        protected SettingRepositoryInterface $settings,
        protected PayPalService $paypal,
    ) {}

    /**
     * صفحة «اختر طريقة الدعم التي تناسبك» — البطاقات الثلاث.
     *
     * @return array<string, mixed>
     */
    public function methodsPage(): array
    {
        $methods = $this->support->activeMethods();

        return [
            'hero' => $this->hero(),
            'section' => [
                'title' => $this->settings->i18n(
                    'support_methods_title',
                    'اختر طريقة الدعم التي تناسبك',
                    'Choose the support method that suits you'
                ),
                'description' => $this->settings->i18n('support_methods_desc'),
            ],
            'categories' => collect(SupportOptions::CATEGORIES)
                ->map(fn (string $category) => $this->category($category, $methods->where('category', $category)))
                ->values()
                ->all(),
            'labels' => $this->labels(),
        ];
    }

    /**
     * قائمة وسائل قسم واحد — شاشة «اختر وسيلة التحويل».
     *
     * @return array<string, mixed>
     *
     * @throws ModelNotFoundException
     */
    public function categoryMethods(string $category): array
    {
        $this->assertCategory($category);

        $methods = $this->support->activeMethods($category);

        return [
            'category' => $this->category($category, $methods),
            'wizard' => $this->wizard(),
            'labels' => $this->labels(),
        ];
    }

    /**
     * تفاصيل وسيلة واحدة — بيانات الحساب/الآيبان/رقم المحفظة أو QR العملة الرقمية.
     *
     * @return array<string, mixed>
     *
     * @throws ModelNotFoundException
     */
    public function method(string $uuid): array
    {
        $method = $this->support->findActiveMethodByUuid($uuid);

        if (! $method) {
            throw (new ModelNotFoundException)->setModel(SupportMethod::class, [$uuid]);
        }

        return [
            'method' => new SupportMethodResource($method),
            'paypal' => $method->isPayPal() ? $this->paypalConfig() : null,
            'labels' => $this->labels(),
        ];
    }

    /**
     * الباقات (لمرة واحدة / شهري / سنوي) + إعدادات المبلغ المخصص.
     *
     * @return array<string, mixed>
     */
    public function plans(): array
    {
        $plans = $this->support->activePlans();

        return [
            'title' => $this->settings->i18n('support_plans_title', 'كيف تريد أن تدعم؟', 'How would you like to support?'),
            'description' => $this->settings->i18n('support_plans_desc'),
            'intervals' => collect(SupportOptions::INTERVALS)
                ->map(fn (string $interval) => [
                    'key' => $interval,
                    'label' => SupportOptions::intervalLabels()[$interval],
                    'is_default' => $interval === $this->defaultInterval(),
                    'plans' => $plans->where('interval', $interval)->map(fn ($plan) => [
                        'uuid' => $plan->uuid,
                        'amount' => (float) $plan->amount,
                        'currency' => $plan->currency,
                        'label' => $plan->getTranslations('label'),
                        'description' => $plan->getTranslations('description'),
                        'is_featured' => $plan->is_featured,
                        'is_recurring' => $plan->isRecurring(),
                    ])->values()->all(),
                ])
                ->values()
                ->all(),
            'custom_amount' => [
                'enabled' => (bool) $this->settings->get('support_custom_amount_enabled', true),
                'min' => (float) ($this->settings->get('support_min_amount', $this->settings->get('min_donation_amount', 5)) ?: 5),
                'max' => (float) ($this->settings->get('support_max_amount', 100000) ?: 100000),
                'placeholder' => $this->settings->i18n('support_custom_amount_label', 'أو أدخل مبلغاً', 'Or enter an amount'),
            ],
            'currency' => (string) ($this->settings->get('support_default_currency', 'USD') ?: 'USD'),
            'paypal' => $this->paypalConfig(),
        ];
    }

    /**
     * تعريف خطوات الويزارد الأربع — الفرونت يرسمها من هنا بدل ما تكون ثابتة بالكود.
     *
     * @return array<string, mixed>
     */
    public function wizard(): array
    {
        $defaults = [
            'method' => ['ar' => 'اختيار المنصة', 'en' => 'Choose platform', 'icon' => 'wallet'],
            'proof' => ['ar' => 'إثبات التبرع', 'en' => 'Donation proof', 'icon' => 'badge-check'],
            'team' => ['ar' => 'دعم الفريق', 'en' => 'Support the team', 'icon' => 'coins'],
            'contact' => ['ar' => 'وسيلة التواصل', 'en' => 'Contact method', 'icon' => 'at-sign'],
        ];

        $steps = [];

        foreach (SupportOptions::STEPS as $index => $key) {
            $steps[] = [
                'key' => $key,
                'order' => $index + 1,
                'label' => $this->settings->i18n(
                    "support_step_{$key}_label",
                    $defaults[$key]['ar'],
                    $defaults[$key]['en'],
                ),
                'icon' => (string) ($this->settings->get("support_step_{$key}_icon", $defaults[$key]['icon']) ?: $defaults[$key]['icon']),
            ];
        }

        return [
            'total' => count($steps),
            'steps' => $steps,
            'progress_label' => $this->settings->i18n('support_step_progress_label', 'الخطوة :current من :total', 'Step :current of :total'),
            'completion_label' => $this->settings->i18n('support_step_completion_label', 'مكتمل بنسبة :percent%', ':percent% complete'),
        ];
    }

    /**
     * إعدادات PayPal اللازمة لتحميل الـ JS SDK بالفرونت.
     *
     * @return array<string, mixed>
     */
    public function paypalConfig(): array
    {
        return [
            'configured' => $this->paypal->isConfigured(),
            'client_id' => $this->paypal->clientId(),
            'mode' => $this->paypal->mode(),
            'currency' => (string) ($this->settings->get('support_default_currency', 'USD') ?: 'USD'),
        ];
    }

    /**
     * @param  Collection<int, SupportMethod>  $methods
     * @return array<string, mixed>
     */
    protected function category(string $category, $methods): array
    {
        $fallback = SupportOptions::categoryLabels()[$category];

        return [
            'key' => $category,
            'title' => $this->settings->i18n("support_cat_{$category}_title", $fallback['ar'], $fallback['en']),
            'description' => $this->settings->i18n("support_cat_{$category}_desc"),
            'icon' => (string) ($this->settings->get("support_cat_{$category}_icon", '') ?: ''),
            'accent' => (string) ($this->settings->get("support_cat_{$category}_accent", '') ?: ''),
            'is_enabled' => (bool) $this->settings->get("support_cat_{$category}_enabled", true),
            'methods_count' => $methods->count(),
            'methods' => SupportMethodResource::collection($methods->values()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function hero(): array
    {
        return [
            'image_url' => MediaUrl::make($this->settings->get('support_header_bg')),
            'title' => $this->settings->i18n('support_hero_title', 'ادعم صوت', 'Support Sawt'),
            'description' => $this->settings->i18n(
                'support_hero_desc',
                'اختر الطريقة الأنسب لك لإتمام تبرعك، وكل مساهمة تتحول إلى قصة تُروى من قلب غزة',
                'Choose the way that suits you best — every contribution becomes a story told from the heart of Gaza'
            ),
        ];
    }

    /**
     * نصوص أزرار وعناوين مشتركة بين شاشات الويزارد.
     *
     * @return array<string, mixed>
     */
    protected function labels(): array
    {
        return [
            'continue' => $this->settings->i18n('support_continue_label', 'المتابعة', 'Continue'),
            'back' => $this->settings->i18n('support_back_label', 'رجوع', 'Back'),
            'submit' => $this->settings->i18n('support_submit_label', 'إرسال', 'Submit'),
            'copy' => $this->settings->i18n('support_copy_label', 'نسخ', 'Copy'),
            'copied' => $this->settings->i18n('support_copied_label', 'تم النسخ', 'Copied'),
            'choose_method' => $this->settings->i18n('support_choose_method_label', 'اختر وسيلة التحويل:', 'Choose a transfer method:'),
            'proof_hint' => $this->settings->i18n(
                'support_proof_hint',
                'ارفع لقطة شاشة واضحة لعملية التحويل ليتم توثيق تبرعك',
                'Upload a clear screenshot of the transfer so we can verify your donation'
            ),
            'success_title' => $this->settings->i18n('support_success_title', 'شكراً لدعمك!', 'Thank you for your support!'),
            'success_message' => $this->settings->i18n(
                'support_success_message',
                'استلمنا طلبك وسيقوم الفريق بمراجعة الإثبات والتواصل معك قريباً.',
                'We received your request. Our team will review the proof and reach out to you soon.'
            ),
        ];
    }

    protected function defaultInterval(): string
    {
        $value = (string) ($this->settings->get('support_default_interval', 'monthly') ?: 'monthly');

        return in_array($value, SupportOptions::INTERVALS, true) ? $value : 'monthly';
    }

    /**
     * @throws ModelNotFoundException
     */
    protected function assertCategory(string $category): void
    {
        if (! in_array($category, SupportOptions::CATEGORIES, true)) {
            throw (new ModelNotFoundException)->setModel(SupportMethod::class, [$category]);
        }
    }
}
