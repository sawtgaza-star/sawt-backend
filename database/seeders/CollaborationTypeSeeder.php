<?php

namespace Database\Seeders;

use App\Models\CollaborationType;
use Illuminate\Database\Seeder;

class CollaborationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'key' => 'creator',
                'title' => ['ar' => 'صانع محتوى', 'en' => 'Content Creator'],
                'description' => [
                    'ar' => 'إذا كنت صانع محتوى أو إعلامي وتريد التعاون مع منصة صوت.',
                    'en' => 'If you are a content creator or media professional and want to collaborate with Sawt.',
                ],
                'sort_order' => 0,
            ],
            [
                'key' => 'sponsorship',
                'title' => ['ar' => 'رعاية أو تمويل', 'en' => 'Sponsorship or Funding'],
                'description' => [
                    'ar' => 'ادعم مشاريع صوت وأصوات صناع المحتوى مباشرة أو عبر شركتك.',
                    'en' => 'Support Sawt projects and content creators directly or through your company.',
                ],
                'sort_order' => 1,
            ],
            [
                'key' => 'partnership',
                'title' => ['ar' => 'شراكة استراتيجية', 'en' => 'Strategic Partnership'],
                'description' => [
                    'ar' => 'بناء شراكة طويلة المدى مع منصة صوت في مجالات الإعلام والتأثير.',
                    'en' => 'Build a long-term partnership with Sawt in media and impact.',
                ],
                'sort_order' => 2,
            ],
            [
                'key' => 'other',
                'title' => ['ar' => 'تعاون آخر', 'en' => 'Other Collaboration'],
                'description' => [
                    'ar' => 'لديك فكرة تعاون مختلفة؟ شاركنا تفاصيلها وسنتواصل معك.',
                    'en' => 'Have a different collaboration idea? Share the details and we will get in touch.',
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($types as $type) {
            CollaborationType::updateOrCreate(
                ['key' => $type['key']],
                [
                    'title' => $type['title'],
                    'description' => $type['description'],
                    'sort_order' => $type['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
