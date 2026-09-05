<?php

namespace Database\Seeders;

use App\Models\MediaServiceItem;
use Illuminate\Database\Seeder;

/**
 * Default Sawt Media services (JSON translatable fields via Spatie).
 */
class MediaServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'slug' => 'photography',
                'number' => '01',
                'sort_order' => 1,
                'title' => ['ar' => 'التصوير الاحترافي', 'en' => 'Professional photography'],
                'tagline' => ['ar' => 'كل صورة تحكي ألف كلمة', 'en' => 'Every photo tells a thousand words'],
                'description' => [
                    'ar' => 'نلتقط اللحظات التي تستحق أن تُرى. بعين خبيرة وأدوات احترافية، نحوّل كل لحظة إلى صورة تعكس جوهر ما تريد قوله للعالم.',
                    'en' => 'We capture moments worth seeing — with expert eyes and pro tools.',
                ],
                'tags' => [
                    'ar' => 'تصوير المنتجات, تصوير الفعاليات',
                    'en' => 'Drone, Personal Branding',
                ],
                'includes' => [
                    'ar' => "جلسة تصوير كاملة مع إعداد الإضاءة والديكور\nتصوير المنتجات والفعاليات بعدسات احترافية\nمعالجة وتنقيح الصور وتسليمها بدقة عالية",
                    'en' => "Full shoot with lighting & set setup\nProduct & event photography with pro lenses\nRetouching and high-precision delivery",
                ],
            ],
            [
                'slug' => 'video',
                'number' => '02',
                'sort_order' => 2,
                'title' => ['ar' => 'إنتاج الفيديوهات', 'en' => 'Video production'],
                'tagline' => ['ar' => 'كل صورة تحكي ألف كلمة', 'en' => 'Every frame tells a story'],
                'description' => [
                    'ar' => 'نصنع فيديوهات احترافية تحكي قصة علامتك التجارية بأسلوب إبداعي يجذب الانتباه ويحقق أهدافك التسويقية.',
                    'en' => 'We produce professional videos that tell your brand story and hit marketing goals.',
                ],
                'tags' => [
                    'ar' => 'تصوير المنتجات, تصوير الفعاليات',
                    'en' => 'Drone, Personal Branding',
                ],
                'includes' => [
                    'ar' => "سيناريو وإخراج احترافي\nتصوير ومونتاج متكامل\nتسليم بصيغ متعددة للنشر",
                    'en' => "Professional script & direction\nFull shoot and edit\nMulti-format delivery for publish",
                ],
            ],
            [
                'slug' => 'graphic-design',
                'number' => '03',
                'sort_order' => 3,
                'title' => ['ar' => 'التصميم الجرافيكي', 'en' => 'Graphic design'],
                'tagline' => ['ar' => 'كل صورة تحكي ألف كلمة', 'en' => 'Design that speaks'],
                'description' => [
                    'ar' => 'حلول تصميم إبداعية من الفكرة حتى التسليم النهائي.',
                    'en' => 'Creative graphic solutions from concept to final delivery.',
                ],
                'tags' => [
                    'ar' => 'تصوير المنتجات, تصوير الفعاليات',
                    'en' => 'Drone, Personal Branding',
                ],
                'includes' => [
                    'ar' => "هوية بصرية متكاملة\nتصاميم سوشيال ومواد مطبوعة\nدليل استخدام العلامة",
                    'en' => "Full visual identity\nSocial & print assets\nBrand usage guide",
                ],
            ],
            [
                'slug' => 'content-creation',
                'number' => '04',
                'sort_order' => 4,
                'title' => ['ar' => 'صناعة المحتوى', 'en' => 'Content creation'],
                'tagline' => ['ar' => 'كل صورة تحكي ألف كلمة', 'en' => 'Content that connects'],
                'description' => [
                    'ar' => 'نصنع محتوى احترافيًا يحكي قصة علامتك التجارية بأسلوب إبداعي.',
                    'en' => 'Professional content that tells your brand story creatively.',
                ],
                'tags' => [
                    'ar' => 'تصوير المنتجات, تصوير الفعاليات',
                    'en' => 'Drone, Personal Branding',
                ],
                'includes' => [
                    'ar' => "خطة محتوى شهرية\nكتابة وإنتاج للمنصات\nقياس أداء وتحسين مستمر",
                    'en' => "Monthly content plan\nWriting & production for platforms\nPerformance tracking and iteration",
                ],
            ],
            [
                'slug' => 'coverage-consulting',
                'number' => '05',
                'sort_order' => 5,
                'title' => ['ar' => 'التغطية والاستشارات', 'en' => 'Coverage & consulting'],
                'tagline' => ['ar' => 'كل صورة تحكي ألف كلمة', 'en' => 'Coverage & counsel'],
                'description' => [
                    'ar' => 'نغطي مؤتمراتك ومبادراتك ونعدّ تقاريرها الإعلامية، ونقدّم تدريبًا واستشارات لبناء حضورك الإعلامي.',
                    'en' => 'Event coverage, media reports, training, and consulting for your media presence.',
                ],
                'tags' => [
                    'ar' => 'تصوير المنتجات, تصوير الفعاليات',
                    'en' => 'Drone, Personal Branding',
                ],
                'includes' => [
                    'ar' => "تغطية فعاليات ومؤتمرات\nتقارير إعلامية مرئية\nاستشارات وتدريب إعلامي",
                    'en' => "Event & conference coverage\nVisual media reports\nMedia consulting & training",
                ],
            ],
        ];

        foreach ($services as $service) {
            MediaServiceItem::updateOrCreate(
                ['slug' => $service['slug']],
                array_merge($service, ['is_active' => true, 'gallery' => [], 'samples' => []])
            );
        }
    }
}
