<?php

namespace App\Http\Requests\Api\Support;

use App\Support\SupportOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * الخطوة 4 — وسيلة التواصل، وبها يُرسَل الطلب للمراجعة.
 */
class StoreContactStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_email' => ['required', 'email', 'max:255'],
            'donor_phone' => ['nullable', 'string', 'max:40'],
            'contact_preference' => ['required', Rule::in(array_keys(SupportOptions::contactPreferences()))],
            'contact_value' => ['nullable', 'string', 'max:255'],
            'subscribe_newsletter' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'donor_name.required' => 'الاسم مطلوب.',
            'donor_email.required' => 'البريد الإلكتروني مطلوب.',
            'donor_email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'contact_preference.required' => 'يجب اختيار وسيلة التواصل.',
            'contact_preference.in' => 'وسيلة التواصل المختارة غير صحيحة.',
        ];
    }

    /**
     * واتساب أو الهاتف يحتاج رقماً — نتحقق منه هنا بدل تعقيد قواعد الحقول.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $preference = $this->input('contact_preference');

            if (in_array($preference, ['whatsapp', 'phone'], true)
                && blank($this->input('contact_value')) && blank($this->input('donor_phone'))) {
                $validator->errors()->add('contact_value', 'يجب إدخال رقم الهاتف لوسيلة التواصل المختارة.');
            }
        });
    }
}
