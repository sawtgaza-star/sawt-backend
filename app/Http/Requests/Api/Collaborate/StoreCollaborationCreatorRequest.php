<?php

namespace App\Http\Requests\Api\Collaborate;

use App\Http\Requests\Api\Collaborate\Concerns\ProvidesCollaborationValidationResponse;
use App\Models\CreatorJoinRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCollaborationCreatorRequest extends FormRequest
{
    use ProvidesCollaborationValidationResponse;

    public function authorize(): bool
    {
        return true;
    }

    protected function collaborationValidationSummary(): string
    {
        return 'يرجى التحقق من بيانات طلب التعاون كصانع محتوى.';
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => Str::lower(trim((string) $this->input('email'))),
            ]);
        }

        foreach (['content_types', 'socials'] as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $decoded = json_decode($this->input($field), true);
                if (is_array($decoded)) {
                    $this->merge([$field => $decoded]);
                }
            }
        }

        if ($this->has('terms_accepted')) {
            $this->merge([
                'terms_accepted' => filter_var($this->input('terms_accepted'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'content_types' => ['required', 'array', 'min:1'],
            'content_types.*' => ['required', 'string', 'max:80'],
            'followers_count' => ['required', 'integer', 'min:0'],
            'content_bio' => ['required', 'string', 'max:5000'],
            'socials' => ['nullable', 'array'],
            'socials.*.platform' => ['required_with:socials', 'string', Rule::in(CreatorJoinRequest::PLATFORMS)],
            'socials.*.url' => ['required_with:socials', 'url', 'max:500'],
            'additional_notes' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg,mp4,mov,webm', 'max:5120'],
            'intro_video' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg,mp4,mov,webm', 'max:5120'],
            'terms_accepted' => ['required', 'accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'الاسم الكامل',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'country_code' => 'رمز الدولة',
            'content_types' => 'نوع المحتوى',
            'content_types.*' => 'نوع المحتوى',
            'followers_count' => 'عدد المتابعين',
            'content_bio' => 'نبذة عن المحتوى',
            'socials' => 'مواقع التواصل',
            'socials.*.platform' => 'منصة التواصل',
            'socials.*.url' => 'رابط المنصة',
            'additional_notes' => 'ملاحظات إضافية',
            'attachment' => 'الفيديو / الملف التعريفي',
            'intro_video' => 'الفيديو التعريفي',
            'terms_accepted' => 'الموافقة على الشروط',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'الرجاء إدخال الاسم الكامل.',
            'email.required' => 'الرجاء إدخال البريد الإلكتروني.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'phone.required' => 'الرجاء إدخال رقم الهاتف.',
            'content_types.required' => 'الرجاء اختيار نوع محتوى واحد على الأقل.',
            'content_types.min' => 'الرجاء اختيار نوع محتوى واحد على الأقل.',
            'followers_count.required' => 'الرجاء إدخال عدد المتابعين التقريبي.',
            'followers_count.integer' => 'عدد المتابعين يجب أن يكون رقماً صحيحاً.',
            'followers_count.min' => 'عدد المتابعين لا يمكن أن يكون سالباً.',
            'content_bio.required' => 'الرجاء إدخال نبذة عن محتواك.',
            'content_bio.max' => 'نبذة عن المحتوى يجب ألا تتجاوز 5000 حرف.',
            'socials.*.platform.in' => 'منصة التواصل المختارة غير مدعومة.',
            'socials.*.url.url' => 'رابط منصة التواصل غير صحيح.',
            'socials.*.url.required_with' => 'الرجاء إدخال رابط منصة التواصل.',
            'additional_notes.max' => 'الملاحظات الإضافية يجب ألا تتجاوز 5000 حرف.',
            'attachment.max' => 'حجم الملف كبير جداً (الحد الأقصى 5MB).',
            'attachment.mimes' => 'صيغة الملف غير مدعومة.',
            'intro_video.max' => 'حجم الفيديو كبير جداً (الحد الأقصى 5MB).',
            'intro_video.mimes' => 'صيغة الفيديو غير مدعومة.',
            'terms_accepted.required' => 'يجب الموافقة على شروط الانضمام وسياسة الخصوصية.',
            'terms_accepted.accepted' => 'يجب الموافقة على شروط الانضمام وسياسة الخصوصية.',
        ];
    }
}
