<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // عام
            ['group' => 'general', 'key' => 'site_name', 'value' => 'منصة صوت', 'type' => 'string'],
            ['group' => 'general', 'key' => 'site_description', 'value' => 'منصة إعلامية عربية تدعم صنّاع المحتوى', 'type' => 'text'],
            ['group' => 'general', 'key' => 'default_locale', 'value' => 'ar', 'type' => 'string'],

            // payment
            ['group' => 'payment', 'key' => 'platform_fee_pct', 'value' => '5', 'type' => 'number'],
            ['group' => 'payment', 'key' => 'min_donation_amount', 'value' => '5', 'type' => 'number'],
            ['group' => 'payment', 'key' => 'bank_name', 'value' => 'Bank of Palestine', 'type' => 'string'],
            ['group' => 'payment', 'key' => 'bank_account_owner', 'value' => 'مؤسسة صوت للإعلام', 'type' => 'string'],
            ['group' => 'payment', 'key' => 'bank_account_number', 'value' => '', 'type' => 'string'],
            ['group' => 'payment', 'key' => 'bank_iban', 'value' => '', 'type' => 'string'],
            ['group' => 'payment', 'key' => 'paypal_mode', 'value' => 'sandbox', 'type' => 'string'],

            // finance (fund split)
            ['group' => 'finance', 'key' => 'fund_split_creators_pct', 'value' => '40', 'type' => 'number'],
            ['group' => 'finance', 'key' => 'fund_split_media_pct', 'value' => '35', 'type' => 'number'],
            ['group' => 'finance', 'key' => 'fund_split_support_pct', 'value' => '25', 'type' => 'number'],

            // contact
            ['group' => 'contact', 'key' => 'contact_phone', 'value' => '', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'contact_email', 'value' => '', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'support_whatsapp', 'value' => '', 'type' => 'string'],

            // social
            ['group' => 'social', 'key' => 'instagram_url', 'value' => '', 'type' => 'string'],
            ['group' => 'social', 'key' => 'twitter_url', 'value' => '', 'type' => 'string'],
            ['group' => 'social', 'key' => 'telegram_url', 'value' => '', 'type' => 'string'],
            ['group' => 'social', 'key' => 'facebook_url', 'value' => '', 'type' => 'string'],
            ['group' => 'social', 'key' => 'linkedin_url', 'value' => '', 'type' => 'string'],
            ['group' => 'social', 'key' => 'youtube_url', 'value' => '', 'type' => 'string'],

            // platform stats (تظهر بصفحة صناع المحتوى)
            ['group' => 'stats', 'key' => 'reach_count', 'value' => '4000000', 'type' => 'number'],
            ['group' => 'stats', 'key' => 'supporters_count', 'value' => '250000', 'type' => 'number'],
            ['group' => 'stats', 'key' => 'collaborations_count', 'value' => '500', 'type' => 'number'],
            ['group' => 'stats', 'key' => 'active_creators_count', 'value' => '45', 'type' => 'number'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
