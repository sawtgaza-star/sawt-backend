<?php

namespace App\Http\Requests\Api\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * إنشاء اشتراك دعم دوري عبر PayPal (شهري / سنوي).
 */
class CreateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_uuid' => ['nullable', 'string', 'exists:support_plans,uuid'],
            'interval' => ['required_without:plan_uuid', 'nullable', Rule::in(['monthly', 'yearly'])],
            'amount' => ['required_without:plan_uuid', 'nullable', 'numeric', 'min:1', 'max:1000000'],
            'currency' => ['nullable', 'string', 'size:3'],
            'subscriber_name' => ['nullable', 'string', 'max:255'],
            'subscriber_email' => ['nullable', 'email', 'max:255'],
            'return_url' => ['nullable', 'url', 'max:2048'],
            'cancel_url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_uuid.exists' => 'الباقة المختارة غير متاحة.',
            'interval.required_without' => 'يجب اختيار دورية الاشتراك أو باقة جاهزة.',
            'interval.in' => 'الاشتراك الدوري يقبل «شهري» أو «سنوي» فقط.',
            'amount.required_without' => 'يجب إدخال المبلغ أو اختيار باقة.',
            'subscriber_email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
        ];
    }
}
