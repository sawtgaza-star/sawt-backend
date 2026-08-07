<?php

namespace App\Http\Requests\Api\Support;

use App\Support\SupportOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * الخطوة 1 من الويزارد — اختيار وسيلة الدعم والمبلغ.
 */
class StartSupportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method_uuid' => ['required', 'string', 'exists:support_methods,uuid'],
            'plan_uuid' => ['nullable', 'string', 'exists:support_plans,uuid'],
            'interval' => ['nullable', Rule::in(SupportOptions::INTERVALS)],
            'amount' => ['required_without:plan_uuid', 'nullable', 'numeric', 'min:1', 'max:1000000'],
            'currency' => ['nullable', 'string', 'size:3'],
            'campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
            'donor_name' => ['nullable', 'string', 'max:255'],
            'donor_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'method_uuid.required' => 'يجب اختيار وسيلة الدعم.',
            'method_uuid.exists' => 'وسيلة الدعم المختارة غير متاحة.',
            'plan_uuid.exists' => 'الباقة المختارة غير متاحة.',
            'interval.in' => 'دورية الدعم غير صحيحة.',
            'amount.required_without' => 'يجب إدخال المبلغ أو اختيار باقة.',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً.',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر.',
            'donor_email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
        ];
    }
}
