<?php

namespace App\Http\Requests\Api\Collaborate;

use App\Http\Requests\Api\Collaborate\Concerns\ProvidesCollaborationValidationResponse;
use App\Models\CollaborationJoinRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCollaborationSponsorshipRequest extends FormRequest
{
    use ProvidesCollaborationValidationResponse;

    public function authorize(): bool
    {
        return true;
    }

    protected function collaborationValidationSummary(): string
    {
        return 'يرجى التحقق من بيانات طلب الرعاية / التمويل.';
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => Str::lower(trim((string) $this->input('email'))),
            ]);
        }

        if ($this->filled('website')) {
            $website = trim((string) $this->input('website'));

            if ($website !== '' && ! preg_match('#^https?://#i', $website)) {
                $website = 'https://'.$website;
            }

            $this->merge(['website' => $website]);
        }

        if ($this->has('support_types') && is_string($this->input('support_types'))) {
            $decoded = json_decode($this->input('support_types'), true);
            if (is_array($decoded)) {
                $this->merge(['support_types' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'website' => ['nullable', 'url', 'max:500'],
            'support_types' => ['required', 'array', 'min:1'],
            'support_types.*' => ['required', 'string', Rule::in(CollaborationJoinRequest::SPONSORSHIP_SUPPORT_TYPES)],
            'organization_bio' => ['required', 'string', 'max:500'],
            'conditions_notes' => ['nullable', 'string', 'max:500'],
            'additional_notes' => ['nullable', 'string', 'max:500'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'company_name' => 'اسم الشركة / المؤسسة',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'country_code' => 'رمز الدولة',
            'website' => 'موقع الشركة / المؤسسة الإلكتروني',
            'support_types' => 'نوع الدعم',
            'support_types.*' => 'نوع الدعم',
            'organization_bio' => 'نبذة عن المؤسسة',
            'conditions_notes' => 'شروط أو مقترحات التعاون',
            'additional_notes' => 'ملاحظات إضافية',
            'attachment' => 'الملف المرفق',
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => 'الرجاء إدخال اسم الشركة / المؤسسة.',
            'email.required' => 'الرجاء إدخال البريد الإلكتروني.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'phone.required' => 'الرجاء إدخال رقم الهاتف.',
            'website.url' => 'رابط موقع الشركة / المؤسسة غير صحيح.',
            'support_types.required' => 'الرجاء اختيار نوع دعم واحد على الأقل.',
            'support_types.min' => 'الرجاء اختيار نوع دعم واحد على الأقل.',
            'support_types.*.in' => 'نوع الدعم المختار غير صالح.',
            'organization_bio.required' => 'الرجاء إدخال نبذة عن مؤسستكم ولماذا ترغبون بالتعاون معنا.',
            'organization_bio.max' => 'نبذة عن المؤسسة يجب ألا تتجاوز 500 حرف.',
            'conditions_notes.max' => 'شروط أو مقترحات التعاون يجب ألا تتجاوز 500 حرف.',
            'additional_notes.max' => 'الملاحظات الإضافية يجب ألا تتجاوز 500 حرف.',
            'attachment.max' => 'حجم الملف كبير جداً (الحد الأقصى 5MB).',
            'attachment.mimes' => 'صيغة الملف غير مدعومة (pdf, png, jpg).',
        ];
    }
}
