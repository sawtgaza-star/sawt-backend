<?php

namespace App\Http\Requests\Api\Collaborate;

use App\Http\Requests\Api\Collaborate\Concerns\ProvidesCollaborationValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreCollaborationOtherRequest extends FormRequest
{
    use ProvidesCollaborationValidationResponse;

    public function authorize(): bool
    {
        return true;
    }

    protected function collaborationValidationSummary(): string
    {
        return 'يرجى التحقق من بيانات طلب التعاون.';
    }

    protected function collaborationErrorKeyAliases(): array
    {
        return [
            'company_name' => 'name',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => Str::lower(trim((string) $this->input('email'))),
            ]);
        }

        $contactName = $this->input('company_name') ?? $this->input('name');

        if ($contactName !== null && $contactName !== '') {
            $this->merge([
                'company_name' => trim((string) $contactName),
                'name' => trim((string) $contactName),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'collaboration_idea' => ['required', 'string', 'max:500'],
            'additional_notes' => ['nullable', 'string', 'max:500'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'company_name' => 'الاسم / اسم المؤسسة',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'country_code' => 'رمز الدولة',
            'collaboration_idea' => 'فكرة التعاون',
            'additional_notes' => 'ملاحظات إضافية',
            'attachment' => 'الملف المرفق',
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => 'الرجاء إدخال الاسم / اسم المؤسسة.',
            'email.required' => 'الرجاء إدخال البريد الإلكتروني.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'phone.required' => 'الرجاء إدخال رقم الهاتف.',
            'collaboration_idea.required' => 'الرجاء إدخال فكرة التعاون التي تقترحها.',
            'collaboration_idea.max' => 'فكرة التعاون يجب ألا تتجاوز 500 حرف.',
            'additional_notes.max' => 'الملاحظات الإضافية يجب ألا تتجاوز 500 حرف.',
            'attachment.max' => 'حجم الملف كبير جداً (الحد الأقصى 5MB).',
            'attachment.mimes' => 'صيغة الملف غير مدعومة (pdf, png, jpg).',
        ];
    }
}
