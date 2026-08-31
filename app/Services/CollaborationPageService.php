<?php

namespace App\Services;

use App\Enums\CollaborationTypeKey;
use App\Models\CollaborationJoinRequest;
use App\Models\CreatorJoinRequest;
use App\Models\CollaborationType;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\MediaUrl;
use Illuminate\Http\UploadedFile;

class CollaborationPageService
{
    public function __construct(
        protected SettingRepositoryInterface $settings,
        protected CollaborationJoinRequestService $joinRequests,
    ) {}

    /**
     * Collaborate landing page (hero + selectable types).
     *
     * @return array<string, mixed>
     */
    public function page(): array
    {
        return [
            'hero' => $this->hero(),
            'types' => CollaborationType::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function hero(): array
    {
        return [
            'image_url' => MediaUrl::make($this->settings->get('collaborate_header_bg')),
            'title' => $this->settings->i18n('collaborate_hero_title', 'تعاون معنا', 'Collaborate with us'),
            'description' => $this->settings->i18n(
                'collaborate_hero_desc',
                'تعرّف على صناع المحتوى في صوت، حيث كل فكرة لها صوت، وكل صانع محتوى له قصة.',
                'Get to know the content creators in Sawt, where every idea has a voice, and every creator has a story.'
            ),
        ];
    }

    /**
     * Sponsorship / funding form wizard config.
     *
     * @return array<string, mixed>
     */
    public function sponsorshipForm(): array
    {
        return [
            'type' => CollaborationTypeKey::Sponsorship->value,
            'hero' => [
                'image_url' => MediaUrl::make($this->settings->get('collaborate_header_bg')),
            ],
            'title' => $this->settings->i18n('collaborate_sponsorship_form_title', 'رعاية أو تمويل', 'Sponsorship or Funding'),
            'steps' => [
                [
                    'number' => 1,
                    'key' => 'entity',
                    'label' => $this->settings->i18n('collaborate_sponsorship_step_1', 'بيانات الجهة', 'Entity details'),
                    'required_fields' => ['company_name', 'email', 'phone'],
                ],
                [
                    'number' => 2,
                    'key' => 'offer',
                    'label' => $this->settings->i18n('collaborate_sponsorship_step_2', 'تفاصيل عرض الدعم', 'Support offer details'),
                    'required_fields' => ['support_types', 'organization_bio'],
                ],
                [
                    'number' => 3,
                    'key' => 'attachments',
                    'label' => $this->settings->i18n('collaborate_sponsorship_step_3', 'تفاصيل إضافية ومرفقات', 'Additional details & attachments'),
                    'required_fields' => [],
                ],
            ],
            'actions' => [
                'next' => $this->settings->i18n('creators_join_next', 'التالي', 'Next'),
                'previous' => $this->settings->i18n('creators_join_prev', 'السابق', 'Previous'),
                'cancel' => $this->settings->i18n('creators_join_cancel', 'إلغاء', 'Cancel'),
                'submit' => $this->settings->i18n('collaborate_sponsorship_submit', 'تسليم الطلب', 'Submit request'),
            ],
            'fields' => [
                'company_name' => $this->settings->i18n('collaborate_sponsorship_company', 'اسم الشركة / المؤسسة', 'Company / organization name'),
                'email' => $this->settings->i18n('collaborate_sponsorship_email', 'البريد الإلكتروني', 'Email'),
                'website' => $this->settings->i18n('collaborate_sponsorship_website', 'موقع الشركة / المؤسسة الإلكتروني', 'Website'),
                'phone' => $this->settings->i18n('collaborate_sponsorship_phone', 'رقم الهاتف للتواصل', 'Contact phone'),
                'support_types' => $this->settings->i18n('collaborate_sponsorship_support_types', 'نوع الدعم الذي ترغبون بتقديمه', 'Type of support you wish to provide'),
                'organization_bio' => $this->settings->i18n('collaborate_sponsorship_bio', 'نبذة عن مؤسستكم ولماذا ترغبون بالتعاون معنا', 'About your organization and why you want to collaborate'),
                'conditions_notes' => $this->settings->i18n('collaborate_sponsorship_conditions', 'هل يوجد شروط أو مقترحات محددة للتعاون؟', 'Any specific conditions or suggestions?'),
                'attachment' => $this->settings->i18n('collaborate_sponsorship_attachment', 'إضافة ملف تعريفي أو عرض تفصيلي', 'Profile or presentation file'),
                'additional_notes' => $this->settings->i18n('collaborate_sponsorship_notes', 'ملاحظات إضافية', 'Additional notes'),
            ],
            'support_types' => collect(CollaborationJoinRequest::SPONSORSHIP_SUPPORT_TYPES)->map(fn (string $key) => [
                'key' => $key,
                'label' => [
                    'ar' => CollaborationJoinRequest::sponsorshipSupportTypeLabel($key),
                    'en' => $key,
                ],
            ])->all(),
            'notice' => $this->settings->i18n(
                'collaborate_sponsorship_notice',
                'سيتم التواصل معك خلال 3–5 أيام عمل بعد استلام الطلب',
                'We will contact you within 3–5 business days after receiving your request'
            ),
            'submit_url' => '/api/v1/pages/collaborate/sponsorship',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitSponsorship(array $data, ?UploadedFile $attachment = null): CollaborationJoinRequest
    {
        return $this->joinRequests->submitSponsorship($data, $attachment);
    }

    /**
     * Strategic partnership form wizard config.
     *
     * @return array<string, mixed>
     */
    public function partnershipForm(): array
    {
        return [
            'type' => CollaborationTypeKey::Partnership->value,
            'hero' => [
                'image_url' => MediaUrl::make($this->settings->get('collaborate_header_bg')),
            ],
            'title' => $this->settings->i18n('collaborate_partnership_form_title', 'شراكة استراتيجية', 'Strategic Partnership'),
            'steps' => [
                [
                    'number' => 1,
                    'key' => 'company',
                    'label' => $this->settings->i18n('collaborate_partnership_step_1', 'بيانات الشركة', 'Company details'),
                    'required_fields' => ['company_name', 'email', 'phone'],
                ],
                [
                    'number' => 2,
                    'key' => 'partnership',
                    'label' => $this->settings->i18n('collaborate_partnership_step_2', 'طبيعة الشراكة', 'Nature of partnership'),
                    'required_fields' => ['partnership_types', 'partnership_goal'],
                ],
                [
                    'number' => 3,
                    'key' => 'attachments',
                    'label' => $this->settings->i18n('collaborate_partnership_step_3', 'مرفقات وملاحظات', 'Attachments & notes'),
                    'required_fields' => [],
                ],
            ],
            'actions' => [
                'next' => $this->settings->i18n('creators_join_next', 'التالي', 'Next'),
                'previous' => $this->settings->i18n('creators_join_prev', 'السابق', 'Previous'),
                'cancel' => $this->settings->i18n('creators_join_cancel', 'إلغاء', 'Cancel'),
                'submit' => $this->settings->i18n('collaborate_partnership_submit', 'تسليم الطلب', 'Submit request'),
            ],
            'fields' => [
                'company_name' => $this->settings->i18n('collaborate_partnership_company', 'اسم الشركة / المؤسسة', 'Company / organization name'),
                'email' => $this->settings->i18n('collaborate_partnership_email', 'البريد الإلكتروني', 'Email'),
                'website' => $this->settings->i18n('collaborate_partnership_website', 'موقع الشركة الإلكتروني', 'Company website'),
                'phone' => $this->settings->i18n('collaborate_partnership_phone', 'رقم الهاتف', 'Phone number'),
                'partnership_types' => $this->settings->i18n('collaborate_partnership_types', 'نوع الشراكة الذي تقترحونها', 'Type of partnership you propose'),
                'partnership_goal' => $this->settings->i18n('collaborate_partnership_goal', 'نبذة عن مؤسستكم وهدف الشراكة', 'About your organization and partnership goal'),
                'attachment' => $this->settings->i18n('collaborate_partnership_attachment', 'إضافة ملف تعريفي', 'Profile file'),
                'additional_notes' => $this->settings->i18n('collaborate_partnership_notes', 'ملاحظات إضافية', 'Additional notes'),
            ],
            'partnership_types' => collect(CollaborationJoinRequest::PARTNERSHIP_TYPES)->map(fn (string $key) => [
                'key' => $key,
                'label' => [
                    'ar' => CollaborationJoinRequest::partnershipTypeLabel($key),
                    'en' => $key,
                ],
            ])->all(),
            'notice' => $this->settings->i18n(
                'collaborate_partnership_notice',
                'سيتم التواصل معك خلال 3–5 أيام عمل بعد استلام الطلب',
                'We will contact you within 3–5 business days after receiving your request'
            ),
            'submit_url' => '/api/v1/pages/collaborate/partnership',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitPartnership(array $data, ?UploadedFile $attachment = null): CollaborationJoinRequest
    {
        return $this->joinRequests->submitPartnership($data, $attachment);
    }

    /**
     * Creator collaborate form wizard config.
     *
     * @return array<string, mixed>
     */
    public function creatorForm(): array
    {
        $types = $this->settings->get('creators_join_content_types', []);
        if (! is_array($types)) {
            $types = [];
        }

        return [
            'type' => CollaborationTypeKey::Creator->value,
            'hero' => [
                'image_url' => MediaUrl::make($this->settings->get('collaborate_header_bg')),
            ],
            'title' => $this->settings->i18n('collaborate_creator_form_title', 'صانع محتوى', 'Content Creator'),
            'steps' => [
                [
                    'number' => 1,
                    'key' => 'personal',
                    'label' => $this->settings->i18n('collaborate_creator_step_1', 'المعلومات الشخصية', 'Personal information'),
                    'required_fields' => ['full_name', 'email', 'phone'],
                ],
                [
                    'number' => 2,
                    'key' => 'content',
                    'label' => $this->settings->i18n('collaborate_creator_step_2', 'تفاصيل المحتوى', 'Content details'),
                    'required_fields' => ['content_types', 'followers_count', 'content_bio'],
                ],
                [
                    'number' => 3,
                    'key' => 'socials',
                    'label' => $this->settings->i18n('collaborate_creator_step_3', 'مواقع التواصل', 'Social media'),
                    'required_fields' => ['terms_accepted'],
                ],
            ],
            'actions' => [
                'next' => $this->settings->i18n('creators_join_next', 'التالي', 'Next'),
                'previous' => $this->settings->i18n('creators_join_prev', 'السابق', 'Previous'),
                'cancel' => $this->settings->i18n('creators_join_cancel', 'إلغاء', 'Cancel'),
                'submit' => $this->settings->i18n('collaborate_creator_submit', 'تسليم الطلب', 'Submit request'),
            ],
            'fields' => [
                'full_name' => $this->settings->i18n('collaborate_creator_full_name', 'الاسم الكامل', 'Full name'),
                'email' => $this->settings->i18n('collaborate_creator_email', 'البريد الإلكتروني', 'Email'),
                'phone' => $this->settings->i18n('collaborate_creator_phone', 'رقم الهاتف', 'Phone number'),
                'content_types' => $this->settings->i18n('collaborate_creator_content_types', 'نوع المحتوى الذي تنتجه', 'Type of content you produce'),
                'followers_count' => $this->settings->i18n('collaborate_creator_followers', 'عدد المتابعين التقريبي في المنصة الواحدة (الأعلى شهرة)', 'Approximate followers on your main platform'),
                'content_bio' => $this->settings->i18n('collaborate_creator_bio', 'نبذة عن محتواك', 'About your content'),
                'socials' => $this->settings->i18n('collaborate_creator_socials', 'روابط مواقع التواصل الاجتماعي', 'Social media links'),
                'additional_notes' => $this->settings->i18n('collaborate_creator_notes', 'ملاحظات إضافية', 'Additional notes'),
                'attachment' => $this->settings->i18n('collaborate_creator_intro', 'فيديو تعريفي عنك ولماذا تريد التعاون مع المنصة؟', 'Intro video about you and why you want to collaborate'),
                'terms_accepted' => $this->settings->i18n('collaborate_creator_terms', 'أوافق على شروط الانضمام وسياسة الخصوصية لمنصة صوت', 'I agree to the join terms and privacy policy'),
            ],
            'content_types' => collect($types)->values()->map(fn (array $type) => [
                'key' => (string) ($type['key'] ?? ''),
                'label' => [
                    'ar' => (string) ($type['label_ar'] ?? ''),
                    'en' => (string) ($type['label_en'] ?? ''),
                ],
            ])->filter(fn (array $type) => $type['key'] !== '')->values()->all(),
            'platforms' => collect(CreatorJoinRequest::PLATFORMS)->map(fn (string $platform) => [
                'key' => $platform,
            ])->all(),
            'notice' => $this->settings->i18n(
                'collaborate_creator_notice',
                'سيتم التواصل معك خلال 3–5 أيام عمل بعد استلام الطلب',
                'We will contact you within 3–5 business days after receiving your request'
            ),
            'submit_url' => '/api/v1/pages/collaborate/creator',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitCreator(array $data, ?UploadedFile $attachment = null): CollaborationJoinRequest
    {
        return $this->joinRequests->submitCreator($data, $attachment);
    }

    /**
     * Other collaboration form wizard config.
     *
     * @return array<string, mixed>
     */
    public function otherForm(): array
    {
        return [
            'type' => CollaborationTypeKey::Other->value,
            'hero' => [
                'image_url' => MediaUrl::make($this->settings->get('collaborate_header_bg')),
            ],
            'title' => $this->settings->i18n('collaborate_other_form_title', 'تعاون آخر', 'Other Collaboration'),
            'steps' => [
                [
                    'number' => 1,
                    'key' => 'contact',
                    'label' => $this->settings->i18n('collaborate_other_step_1', 'بيانات التواصل', 'Contact details'),
                    'required_fields' => ['name', 'email', 'phone'],
                ],
                [
                    'number' => 2,
                    'key' => 'idea',
                    'label' => $this->settings->i18n('collaborate_other_step_2', 'شرح الفكرة', 'Explain your idea'),
                    'required_fields' => ['collaboration_idea'],
                ],
            ],
            'actions' => [
                'next' => $this->settings->i18n('creators_join_next', 'التالي', 'Next'),
                'previous' => $this->settings->i18n('creators_join_prev', 'السابق', 'Previous'),
                'cancel' => $this->settings->i18n('creators_join_cancel', 'إلغاء', 'Cancel'),
                'submit' => $this->settings->i18n('collaborate_other_submit', 'تسليم الطلب', 'Submit request'),
            ],
            'fields' => [
                'name' => $this->settings->i18n('collaborate_other_name', 'الاسم / اسم المؤسسة', 'Name / organization name'),
                'email' => $this->settings->i18n('collaborate_other_email', 'البريد الإلكتروني', 'Email'),
                'phone' => $this->settings->i18n('collaborate_other_phone', 'رقم الهاتف', 'Phone number'),
                'collaboration_idea' => $this->settings->i18n('collaborate_other_idea', 'ما هي فكرة التعاون التي تقترحها؟', 'What collaboration idea are you proposing?'),
                'attachment' => $this->settings->i18n('collaborate_other_attachment', 'إضافة ملف', 'Add file'),
                'additional_notes' => $this->settings->i18n('collaborate_other_notes', 'ملاحظات إضافية', 'Additional notes'),
            ],
            'notice' => $this->settings->i18n(
                'collaborate_other_notice',
                'سيتم التواصل معك خلال 3–5 أيام عمل بعد استلام الطلب',
                'We will contact you within 3–5 business days after receiving your request'
            ),
            'submit_url' => '/api/v1/pages/collaborate/other',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitOther(array $data, ?UploadedFile $attachment = null): CollaborationJoinRequest
    {
        return $this->joinRequests->submitOther($data, $attachment);
    }
}
