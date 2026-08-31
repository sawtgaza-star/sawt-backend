<?php

namespace App\Http\Requests\Api\Collaborate;

use App\Http\Requests\Api\Collaborate\Concerns\ProvidesCollaborationValidationResponse;
use App\Models\CollaborationJoinRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCollaborationPartnershipRequest extends FormRequest
{
    use ProvidesCollaborationValidationResponse;

    public function authorize(): bool
    {
        return true;
    }

    protected function collaborationValidationSummary(): string
    {
        return 'يرجى التحقق من بيانات طلب الشراكة الاستراتيجية.';
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

        if ($this->has('partnership_types') && is_string($this->input('partnership_types'))) {
            $decoded = json_decode($this->input('partnership_types'), true);
            if (is_array($decoded)) {
                $this->merge(['partnership_types' => $decoded]);
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
            'partnership_types' => ['required', 'array', 'min:1'],
            'partnership_types.*' => ['required', 'string', Rule::in(CollaborationJoinRequest::PARTNERSHIP_TYPES)],
            'partnership_goal' => ['required', 'string', 'max:500'],
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
            'website' => 'موقع الشركة الإلكتروني',
            'partnership_types' => 'نوع الشراكة',
            'partnership_types.*' => 'نوع الشراكة',
            'partnership_goal' => 'نبذة عن المؤسسة وهدف الشراكة',
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
            'website.url' => 'رابط موقع الشركة غير صحيح.',
            'partnership_types.required' => 'الرجاء اختيار نوع شراكة واحد على الأقل.',
            'partnership_types.min' => 'الرجاء اختيار نوع شراكة واحد على الأقل.',
            'partnership_types.*.in' => 'نوع الشراكة المختار غير صالح.',
            'partnership_goal.required' => 'الرجاء إدخال نبذة عن مؤسستكم وهدف الشراكة.',
            'partnership_goal.max' => 'نبذة عن المؤسسة وهدف الشراكة يجب ألا تتجاوز 500 حرف.',
            'additional_notes.max' => 'الملاحظات الإضافية يجب ألا تتجاوز 500 حرف.',
            'attachment.max' => 'حجم الملف كبير جداً (الحد الأقصى 5MB).',
            'attachment.mimes' => 'صيغة الملف غير مدعومة (pdf, png, jpg).',
        ];
    }
}
