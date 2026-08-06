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

            // about page
            ['group' => 'about', 'key' => 'about_hero_title_ar', 'value' => 'صناع الأثر.. الفريق خلف منصة صوت', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_hero_title_en', 'value' => 'Impact Makers.. The Team Behind Sawt Platform', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_hero_desc_ar', 'value' => 'في هذه الصفحة، نشارككم قصة فريق من الناس إلى الناس، رؤيتنا، رسالتنا، وكيف بدأنا لنكون صوتًا حيًّا ومعينًا لمن لا صوت لهم، وكيف منحنا الناس الأمل.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_hero_desc_en', 'value' => 'On this page, we share with you the story of a team from the people to the people — our vision, our message, and how we began to be a living, supportive voice for the voiceless, and how we gave people hope.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_header_ar', 'value' => 'من نحن', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_header_en', 'value' => 'About Sawt', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_intro_ar', 'value' => 'فريق منصة صوت حاضنة لأصوات غزة. لم نبدأ من فكرةٍ خارقة أو خطةٍ مُحكمة، بل من قرارٍ بسيط: أن نكون حاضرِين، نستمع، ونُعلِن صوت غزة للعالم. نحن من الناس ونعيش معاناتهم عن قرب، فرأينا أن الحاجة واضحة فقررنا ألا نصمت. نعمل بدون أن نتكلم نيابةً عن أحد، وبدون وعودٍ تفوق قدراتنا. وظيفتنا توصيل صوت أهل غزة وإيصاله إلى العالم، مع الحفاظ على كرامة الناس وصوتهم. هدفنا أن نكون جسرًا صادقًا بين من يقدّم الانتباه والدعم لصوت غزة ومن ينتظر من يسمعهم حقًا. اليوم نحن أكثر من فكرة آمن بها شخص واحد؛ نحن حاضنة لأصوات غزة، فريق عمل متكامل.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_intro_en', 'value' => 'The Sawt platform team is an incubator for the voices of Gaza. We did not start from an extraordinary idea or a tight plan, but from a simple decision: to be present, to listen, and to announce the voice of Gaza to the world.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_platform_question_ar', 'value' => 'ما الذي يدفعنا لنكون صوتك؟', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_platform_question_en', 'value' => 'What drives us to be your voice?', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_platform_desc_ar', 'value' => 'نؤمن أن لكل إنسان قصة تستحق أن تُروى، لذلك جاءت صوت لتكون مساحة حرة للتعبير، حيث يلتقي الأفراد لمشاركة تجاربهم وأفكارهم بصدق. نساعدك على إيصال صوتك إلى الآخرين، ونمنح المحتوى الإنساني مساحة حقيقية ليُرى، ويُسمع، ويترك أثرًا.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_platform_desc_en', 'value' => 'We believe every person has a story worth telling. That\'s why Sawt was created as a free space for expression.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_core_values_subtitle_ar', 'value' => 'قيمنا هي الأساس الذي نبني عليه صوت، وهي ما يقود طريقة عملنا وتطويرنا المستمر', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_core_values_subtitle_en', 'value' => 'Our values are the foundation on which we build Sawt, and they guide the way we work and continuously improve.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_core_values', 'value' => json_encode([
                ['title_ar' => 'التمكين', 'title_en' => 'Empowerment', 'desc_ar' => 'نسعى لأن نكون منبرًا يُمكّن الإنسان، ويصنع تأثيرًا إيجابيًا حقيقيًا يساهم في إيصال صوتنا وصوت المجتمع إلى العالم.', 'desc_en' => 'We strive to be a platform that empowers people and creates a real positive impact.'],
                ['title_ar' => 'الموثوقية', 'title_en' => 'Reliability', 'desc_ar' => 'نحرص على صون الأمانة الإعلامية وحماية الرواية وتوثيقها، معتمدين على معايير أخلاقية راسخة في كل ما ننشر.', 'desc_en' => 'We are committed to preserving media integrity, protecting and documenting the narrative.'],
                ['title_ar' => 'الشراكة', 'title_en' => 'Partnership', 'desc_ar' => 'نؤمن أن قوة "صوت غزة" نبع من تلاحم المجتمع، ونعمل كجسر يصل بين الأصوات المختلفة لتعزيز الدعم المتبادل.', 'desc_en' => 'We believe the strength of "Sawt Gaza" springs from the cohesion of the community.'],
                ['title_ar' => 'الابتكار', 'title_en' => 'Innovation', 'desc_ar' => 'نلتزم بنقل الأخبار والقصص بمهنية عالية وأمانة صحفية، لنكون المصدر الموثوق الذي يعبّر عن الواقع بدقة.', 'desc_en' => 'We commit to delivering news and stories with high professionalism and journalistic integrity.'],
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            ['group' => 'about', 'key' => 'about_story_subtitle_ar', 'value' => 'قيمنا هي الأساس الذي نبني عليه صوت، وهي ما يقود طريقة عملنا وتطويرنا المستمر', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_story_subtitle_en', 'value' => 'Our values are the foundation on which we build Sawt, and they guide the way we work and continuously improve.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_story_cards', 'value' => json_encode([
                ['title_ar' => 'التأثير', 'title_en' => 'Impact', 'desc_ar' => 'منذ انطلاقنا، استطعنا إيصال أصوات الآلاف من الأشخاص الذين كانوا صامتين، وكشفنا حقائق عديدة تم إخفاؤها عن الرأي العام. قصصنا وصلت لملايين المتابعين، وساهمت في لفت انتباه العالم إلى قضايا مهمشة.', 'desc_en' => 'Since we began, we have managed to carry the voices of thousands of people who were silenced.'],
                ['title_ar' => 'ما نقدم', 'title_en' => 'What We Offer', 'desc_ar' => 'نحن نقدم إعلامًا حقيقيًا يعتمد على القصص الحقيقية والأصوات الصادقة، بعيدًا عن ضغوط الإعلام التقليدي والسرديات الرسمية.', 'desc_en' => 'We provide genuine media built on real stories and honest voices.'],
                ['title_ar' => 'رحلتنا', 'title_en' => 'Our Journey', 'desc_ar' => 'بدأت رحلة "صوت" في ظل ظروف صعبة، حيث كانت الكثير من القصص الحقيقية مخفية والأصوات الصادقة مكتومة تحت ضغوط الإعلام التقليدي والسرديات الرسمية.', 'desc_en' => 'The journey of "Sawt" began under difficult circumstances.'],
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            ['group' => 'about', 'key' => 'about_join_title_ar', 'value' => 'قد تكون قصتك بداية التغيير', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_join_title_en', 'value' => 'Your story could be the start of change', 'type' => 'string'],
            ['group' => 'about', 'key' => 'about_join_desc_ar', 'value' => 'كل صوت مهم، إذا كانت لديك قصة تستحق أن تُسمع فإن صوت ستدعمك من أول محادثة إلى التأثير العام.', 'type' => 'text'],
            ['group' => 'about', 'key' => 'about_join_desc_en', 'value' => 'Every voice matters. If you have a story worth hearing, Sawt will support you from the first conversation to public impact.', 'type' => 'text'],

            // reels (إنستغرام)
            ['group' => 'reels', 'key' => 'reels_enabled', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'reels', 'key' => 'instagram_user_id', 'value' => '', 'type' => 'string'],
            ['group' => 'reels', 'key' => 'instagram_access_token', 'value' => '', 'type' => 'string'],
            ['group' => 'reels', 'key' => 'instagram_cache_ttl', 'value' => '300', 'type' => 'number'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
