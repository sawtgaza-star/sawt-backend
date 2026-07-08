<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * صفحة إعدادات واحدة (بدل CRUD تقليدي) — كل إعدادات المنصة مجمّعة بتابات حسب
 * عمود "group" بجدول settings، ومفاتيحها مطابقة تماماً لما يزرعه SettingSeeder
 * عشان أول ما تشغّل db:seed القيم تظهر جاهزة بالفورم.
 *
 * القيم بتتخزن/تتقرأ عبر App\Models\Setting::get() / ::set() فيهم طبقة الكاش الجاهزة.
 */
class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return __('Platform Settings');
    }

    /**
     * key => [group, type, default]
     */
    protected function fieldMeta(): array
    {
        return [
            // عام
            'site_name' => ['general', 'string', 'منصة صوت'],
            'site_description' => ['general', 'text', ''],
            'default_locale' => ['general', 'string', 'ar'],

            // payment
            'platform_fee_pct' => ['payment', 'number', 5],
            'min_donation_amount' => ['payment', 'number', 5],
            'bank_name' => ['payment', 'string', 'Bank of Palestine'],
            'bank_account_owner' => ['payment', 'string', 'مؤسسة صوت للإعلام'],
            'bank_account_number' => ['payment', 'string', ''],
            'bank_iban' => ['payment', 'string', ''],
            'paypal_mode' => ['payment', 'string', 'sandbox'],

            // finance (fund split)
            'fund_split_creators_pct' => ['finance', 'number', 40],
            'fund_split_media_pct' => ['finance', 'number', 35],
            'fund_split_support_pct' => ['finance', 'number', 25],

            // contact
            'contact_phone' => ['contact', 'string', ''],
            'contact_email' => ['contact', 'string', ''],
            'support_whatsapp' => ['contact', 'string', ''],

            // social
            'facebook_url' => ['social', 'string', ''],
            'instagram_url' => ['social', 'string', ''],
            'twitter_url' => ['social', 'string', ''],
            'linkedin_url' => ['social', 'string', ''],
            'telegram_url' => ['social', 'string', ''],
            'youtube_url' => ['social', 'string', ''],

            // stats (تظهر بصفحة صنّاع المحتوى بالموقع العام)
            'reach_count' => ['stats', 'number', 4000000],
            'supporters_count' => ['stats', 'number', 250000],
            'collaborations_count' => ['stats', 'number', 500],
            'active_creators_count' => ['stats', 'number', 45],
        ];
    }

    public function mount(): void
    {
        $values = [];

        foreach ($this->fieldMeta() as $key => [$group, $type, $default]) {
            $values[$key] = Setting::get($key, $default);
        }

        $this->form->fill($values);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Settings')->columnSpanFull()->tabs([

                Forms\Components\Tabs\Tab::make('عام')->icon('heroicon-o-globe-alt')->schema([
                    Forms\Components\TextInput::make('site_name')->label('اسم المنصة')->required(),
                    Forms\Components\Textarea::make('site_description')->label('وصف المنصة')->rows(3),
                    Forms\Components\Select::make('default_locale')
                        ->label('اللغة الافتراضية')
                        ->options(['ar' => 'العربية', 'en' => 'English'])
                        ->required(),
                ]),

                Forms\Components\Tabs\Tab::make('الدفع')->icon('heroicon-o-credit-card')->schema([
                    Forms\Components\TextInput::make('platform_fee_pct')
                        ->label('نسبة عمولة المنصة (%)')->numeric()->minValue(0)->maxValue(100)->suffix('%')->required(),
                    Forms\Components\TextInput::make('min_donation_amount')
                        ->label('الحد الأدنى للتبرع')->numeric()->prefix('$'),
                    Forms\Components\TextInput::make('bank_name')->label('اسم البنك'),
                    Forms\Components\TextInput::make('bank_account_owner')->label('اسم صاحب الحساب'),
                    Forms\Components\TextInput::make('bank_account_number')->label('رقم الحساب'),
                    Forms\Components\TextInput::make('bank_iban')->label('IBAN'),
                    Forms\Components\Select::make('paypal_mode')
                        ->label('وضع PayPal')
                        ->options(['sandbox' => 'تجريبي (Sandbox)', 'live' => 'مباشر (Live)']),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('توزيع الأموال')->icon('heroicon-o-chart-pie')->schema([
                    Forms\Components\TextInput::make('fund_split_creators_pct')->label('نسبة صنّاع المحتوى (%)')->numeric()->suffix('%')->required(),
                    Forms\Components\TextInput::make('fund_split_media_pct')->label('نسبة الإنتاج الإعلامي (%)')->numeric()->suffix('%')->required(),
                    Forms\Components\TextInput::make('fund_split_support_pct')->label('نسبة الدعم التشغيلي (%)')->numeric()->suffix('%')->required(),
                ])->columns(3),

                Forms\Components\Tabs\Tab::make('التواصل')->icon('heroicon-o-envelope')->schema([
                    Forms\Components\TextInput::make('contact_email')->label('بريد التواصل')->email(),
                    Forms\Components\TextInput::make('contact_phone')->label('هاتف التواصل'),
                    Forms\Components\TextInput::make('support_whatsapp')->label('واتساب الدعم'),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('التواصل الاجتماعي')->icon('heroicon-o-share')->schema([
                    Forms\Components\TextInput::make('facebook_url')->label('Facebook')->url(),
                    Forms\Components\TextInput::make('instagram_url')->label('Instagram')->url(),
                    Forms\Components\TextInput::make('twitter_url')->label('X / Twitter')->url(),
                    Forms\Components\TextInput::make('linkedin_url')->label('LinkedIn')->url(),
                    Forms\Components\TextInput::make('telegram_url')->label('Telegram')->url(),
                    Forms\Components\TextInput::make('youtube_url')->label('YouTube')->url(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('إحصائيات الواجهة')->icon('heroicon-o-presentation-chart-line')->schema([
                    Forms\Components\TextInput::make('reach_count')->label('عدد الوصول')->numeric(),
                    Forms\Components\TextInput::make('supporters_count')->label('عدد الداعمين')->numeric(),
                    Forms\Components\TextInput::make('collaborations_count')->label('عدد التعاونات')->numeric(),
                    Forms\Components\TextInput::make('active_creators_count')->label('عدد صنّاع المحتوى النشطين')->numeric(),
                ])->columns(2),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($this->fieldMeta() as $key => [$group, $type, $default]) {
            Setting::set($key, $state[$key] ?? $default, group: $group, type: $type);
        }

        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }
}
