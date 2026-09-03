<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseTrainer;
use Illuminate\Database\Seeder;

/**
 * Seeds sample offline course «تصميم الجرافيك» with trainer + design category.
 * Safe to re-run (updateOrCreate on slug keys).
 */
class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $category = CourseCategory::query()->updateOrCreate(
            ['slug' => 'design'],
            [
                'name' => ['ar' => 'التصميم', 'en' => 'Design'],
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $trainer = CourseTrainer::query()->updateOrCreate(
            ['email' => 'trainer.graphic@sawtgaza.local'],
            [
                'name' => [
                    'ar' => 'محمد العارف',
                    'en' => 'Mohammed Al-Aref',
                ],
                'title' => [
                    'ar' => 'متخصص في صناعة المحتوى الرقمي والإنتاج الإعلامي',
                    'en' => 'Digital content & media production specialist',
                ],
                'bio' => [
                    'ar' => 'مدرب ومتخصص في صناعة المحتوى الرقمي والإنتاج الإعلامي، يمتلك خبرة عملية في تطوير الأفكار، وكتابة السكربت، وإنتاج المحتوى الهادف.',
                    'en' => 'Trainer and specialist in digital content and media production.',
                ],
                'experience' => [
                    'ar' => '8 سنوات',
                    'en' => '8 years',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Course::query()->updateOrCreate(
            ['slug' => 'graphic-design'],
            [
                'course_category_id' => $category->id,
                'trainer_id' => $trainer->id,
                'title' => [
                    'ar' => 'تصميم الجرافيك',
                    'en' => 'Graphic Design',
                ],
                'description' => [
                    'ar' => 'برنامج تدريبي عملي في تصميم الجرافيك، تتعلم فيه أساسيات التصميم ونظرية الألوان والعمل على أشهر برامج التصميم، وصولًا إلى بناء هوية بصرية متكاملة ومعرض أعمال يفتح لك أبواب سوق العمل.',
                    'en' => 'A practical graphic design program covering design foundations, color theory, and major design tools — through to a full visual identity and portfolio.',
                ],
                'level' => 'beginner',
                'status' => 'published',
                'delivery_mode' => 'offline',
                'duration_weeks' => 4,
                'duration_hours' => '15 ساعة',
                'sessions_hours' => '4 ساعات',
                'rating' => 5,
                'is_coming_soon' => false,
                'max_seats' => 25,
                'starts_at' => '2026-09-07 00:00:00',
                'registration_ends_at' => '2026-08-30 23:59:59',
                'objectives' => [
                    ['title_ar' => 'أساسيات التصميم', 'title_en' => 'Design basics', 'desc_ar' => 'تمكين المشاركين من أساسيات التصميم ونظرية الألوان.', 'desc_en' => 'Master design foundations and color theory.'],
                    ['title_ar' => 'إتقان أدوات التصميم', 'title_en' => 'Design tools', 'desc_ar' => 'احتراف برامج التصميم الأساسية مثل فوتوشوب وإليستريتور.', 'desc_en' => 'Master Photoshop and Illustrator.'],
                    ['title_ar' => 'التايبوغرافي والخط العربي', 'title_en' => 'Typography', 'desc_ar' => 'توظيف الخطوط العربية واللاتينية بشكل احترافي.', 'desc_en' => 'Use Arabic and Latin type professionally.'],
                    ['title_ar' => 'بناء الهوية البصرية', 'title_en' => 'Visual identity', 'desc_ar' => 'تصميم هويات بصرية متكاملة للعلامات التجارية.', 'desc_en' => 'Build complete brand visual identities.'],
                    ['title_ar' => 'تصميم محتوى السوشيال ميديا', 'title_en' => 'Social media design', 'desc_ar' => 'إنتاج تصاميم جذابة لمنصات التواصل الاجتماعي.', 'desc_en' => 'Create engaging social media designs.'],
                    ['title_ar' => 'معرض أعمال احترافي', 'title_en' => 'Portfolio', 'desc_ar' => 'بناء معرض أعمال يؤهلك لسوق العمل.', 'desc_en' => 'Build a portfolio ready for the job market.'],
                ],
                'modules' => [
                    [
                        'title_ar' => 'أساسيات التصميم ونظرية الألوان',
                        'title_en' => 'Design basics & color theory',
                        'lessons' => [
                            ['title_ar' => 'مبادئ التصميم الجرافيكي', 'title_en' => 'Graphic design principles', 'duration' => '15 دقيقة'],
                            ['title_ar' => 'نظرية الألوان وتطبيقاتها', 'title_en' => 'Color theory & applications', 'duration' => '20 دقيقة'],
                            ['title_ar' => 'اختبار قصير', 'title_en' => 'Short quiz', 'duration' => '10 دقيقة'],
                        ],
                    ],
                    ['title_ar' => 'التصميم باستخدام برنامج فوتوشوب', 'title_en' => 'Design with Photoshop', 'lessons' => []],
                    ['title_ar' => 'الرسم المتجهي باستخدام إليستريتور', 'title_en' => 'Vector drawing with Illustrator', 'lessons' => []],
                    ['title_ar' => 'التايبوغرافي والخطوط العربية', 'title_en' => 'Typography & Arabic fonts', 'lessons' => []],
                    ['title_ar' => 'تصميم الهوية البصرية المتكاملة', 'title_en' => 'Complete visual identity', 'lessons' => []],
                    ['title_ar' => 'تصميم منشورات السوشيال ميديا', 'title_en' => 'Social media posts', 'lessons' => []],
                    ['title_ar' => 'مشروع نهائي: هوية بصرية متكاملة', 'title_en' => 'Final project: full visual identity', 'lessons' => []],
                ],
                'outcomes_before' => [
                    ['ar' => 'لديك شغف بالتصميم لكن لا تعرف من أين تبدأ.', 'en' => 'You love design but do not know where to start.'],
                    ['ar' => 'لا تمتلك خبرة في برامج التصميم.', 'en' => 'You have no experience with design tools.'],
                    ['ar' => 'تواجه صعوبة في اختيار الألوان والخطوط.', 'en' => 'Choosing colors and fonts is hard.'],
                    ['ar' => 'لا تعرف كيف تبني هوية بصرية متكاملة.', 'en' => 'You do not know how to build a full visual identity.'],
                ],
                'outcomes_after' => [
                    ['ar' => 'تمتلك تصاميم احترافية من إنتاجك.', 'en' => 'You have professional designs of your own.'],
                    ['ar' => 'تحترف برامج التصميم الأساسية.', 'en' => 'You master core design tools.'],
                    ['ar' => 'تتقن بناء الهويات البصرية للعلامات.', 'en' => 'You can build brand visual identities.'],
                    ['ar' => 'تمتلك ملف أعمال (portfolio).', 'en' => 'You have a portfolio.'],
                    ['ar' => 'تعرف كيف تصمم محتوى يجذب الجمهور.', 'en' => 'You design content that attracts audiences.'],
                ],
                'benefits' => [
                    ['ar' => 'تدريب عملي بإشراف مدربين متخصصين.', 'en' => 'Hands-on training with specialized mentors.'],
                    ['ar' => 'مراجعات وتغذية راجعة لتطوير مستواك.', 'en' => 'Reviews and feedback to improve.'],
                    ['ar' => 'شهادة إتمام بعد اجتياز البرنامج.', 'en' => 'Certificate after completion.'],
                    ['ar' => 'مشروع احترافي يضاف إلى معرض أعمالك.', 'en' => 'A professional project for your portfolio.'],
                    ['ar' => 'فرصة لنشر أفضل الأعمال عبر منصة صوت.', 'en' => 'Chance to publish top work on Sawt.'],
                    ['ar' => 'الانضمام إلى مجتمع حاضنة صوت وفرص مستقبلية للتطوير.', 'en' => 'Join the Sawt Incubator community.'],
                ],
                'requirements' => [
                    ['ar' => 'أن يكون المتقدم شغوفاً بصناعة المحتوى والتعلّم.', 'en' => 'Passion for content and learning.'],
                    ['ar' => 'الالتزام بحضور جميع الجلسات والأنشطة التدريبية.', 'en' => 'Attend all sessions and activities.'],
                    ['ar' => 'امتلاك هاتف ذكي صالح للتصوير.', 'en' => 'A smartphone suitable for shooting.'],
                    ['ar' => 'الاستعداد لتنفيذ المهام والمشروع النهائي.', 'en' => 'Ready to complete tasks and the final project.'],
                    ['ar' => 'لا يشترط وجود خبرة سابقة.', 'en' => 'No prior experience required.'],
                ],
                'selection_steps' => [
                    ['title_ar' => 'استلام طلبات التقديم', 'title_en' => 'Receive applications', 'desc_ar' => 'تعبئة نموذج التسجيل بشكل كامل.', 'desc_en' => 'Complete the registration form.'],
                    ['title_ar' => 'مراجعة الطلبات', 'title_en' => 'Review applications', 'desc_ar' => 'مراجعة الطلبات وتقييم مدى ملاءمتها للبرنامج.', 'desc_en' => 'Review fit for the program.'],
                    ['title_ar' => 'تقييم المتقدمين', 'title_en' => 'Evaluate applicants', 'desc_ar' => 'تقييم الدافع والاهتمام بصناعة المحتوى.', 'desc_en' => 'Assess motivation and interest.'],
                    ['title_ar' => 'المقابلة', 'title_en' => 'Interview', 'desc_ar' => 'إجراء مقابلة قصيرة عند الحاجة.', 'desc_en' => 'Short interview when needed.'],
                    ['title_ar' => 'إعلان نتائج القبول', 'title_en' => 'Announce results', 'desc_ar' => 'اختيار أفضل المتقدمين وإبلاغهم بنتيجة القبول.', 'desc_en' => 'Select and notify accepted applicants.'],
                ],
            ]
        );
    }
}
