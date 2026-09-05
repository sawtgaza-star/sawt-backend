<?php

namespace Database\Seeders;

use App\Models\MediaServiceItem;
use App\Models\MediaWork;
use Illuminate\Database\Seeder;

/**
 * Example portfolio work matching /media/works/film (JSON translatable fields).
 */
class MediaWorkSeeder extends Seeder
{
    public function run(): void
    {
        $videoId = MediaServiceItem::query()->where('slug', 'video')->value('id');

        MediaWork::updateOrCreate(
            ['slug' => 'film'],
            [
                'media_service_id' => $videoId,
                'title' => ['ar' => 'فيلم تعريفي لشركة أفق', 'en' => 'Intro film for Ufuq company'],
                'category' => ['ar' => 'إنتاج', 'en' => 'Production'],
                'tag' => ['ar' => 'تصميم التطبيقات', 'en' => 'App design'],
                'date' => ['ar' => '2026 أبريل', 'en' => 'April 2026'],
                'summary' => [
                    'ar' => 'بناء هوية متكاملة لمؤسسة الشباب، تعكس طموح الجيل الجديد وقيم العمل المؤسسي الاحترافي.',
                    'en' => 'A complete identity for a youth institution — ambition and professional values.',
                ],
                'highlights' => [
                    ['value' => '+60%', 'label_ar' => 'زيادة في التفاعل على المنصات', 'label_en' => 'Increase in platform engagement'],
                    ['value' => '+590 M', 'label_ar' => 'زيادة في التفاعل على المنصات', 'label_en' => 'Increase in platform engagement'],
                ],
                'about' => [
                    'ar' => 'عملنا على تطوير هوية بصرية شاملة تضمنت تصميم الشعار ودليل الهوية الكامل وقوالب التواصل الاجتماعي والمطبوعات الرسمية. كان الهدف إيجاد هوية تجمع بين الاحترافية والحيوية لتناسب جمهور الشباب.',
                    'en' => 'We built a full visual identity — logo, brand guide, social templates, and official print — balancing professionalism and energy for a youth audience.',
                ],
                'challenges' => [
                    'ar' => "إيجاد توازن بين الطابع المؤسسي الرسمي والروح\nالشبابية النابضة مع ضمان قابلية تطبيق الهوية على جميع الوسائط.",
                    'en' => "Balance institutional tone with youthful energy\nEnsure the identity works across all media.",
                ],
                'solutions' => [
                    'ar' => "اعتمدنا على نظام ألوان ثنائي المزاج يجمع الرسوخ والحيوية\nمع خط عربي عصري يحمل الجرأة والوضوح في آنٍ معاً.",
                    'en' => "A dual-mood color system combining solidity and vitality\nA modern Arabic typeface with clarity and boldness.",
                ],
                'stages' => [
                    ['title_ar' => 'الاكتشاف', 'title_en' => 'Discovery', 'body_ar' => 'فهم الجمهور والأهداف.', 'body_en' => 'Understand audience and goals.'],
                    ['title_ar' => 'التصميم', 'title_en' => 'Design', 'body_ar' => 'بناء الهوية والمخرجات.', 'body_en' => 'Build identity and deliverables.'],
                    ['title_ar' => 'التسليم', 'title_en' => 'Delivery', 'body_ar' => 'تسليم الدليل والقوالب.', 'body_en' => 'Deliver guide and templates.'],
                ],
                'client_name' => 'عميل أفق',
                'client_role' => ['ar' => 'مدير التسويق', 'en' => 'Marketing manager'],
                'client_quote' => [
                    'ar' => 'فريق صوت ميديا فهم رؤيتنا وقدّم هوية تليق بطموحنا.',
                    'en' => 'Sawt Media understood our vision and delivered an identity that matches our ambition.',
                ],
                'results' => [
                    ['value' => '+45', 'label_ar' => 'حلقة منتجة', 'label_en' => 'Episodes produced'],
                    ['value' => '+5m', 'label_ar' => 'زيادة في التفاعل على المنصات', 'label_en' => 'Platform engagement increase'],
                    ['value' => '+30%', 'label_ar' => 'زيادة التفاعل', 'label_en' => 'Engagement increase'],
                ],
                'gallery' => [],
                'show_on_landing' => true,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );
    }
}
