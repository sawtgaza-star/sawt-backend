<?php

namespace App\Http\Requests\Api\Support;

use App\Support\SupportOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * «ادعم صوت» السريع — مبلغ + دورية ثم تحويل مباشر لـ PayPal.
 */
class StartCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_uuid' => ['nullable', 'string', 'exists:support_plans,uuid'],
            'interval' => ['nullable', Rule::in(SupportOptions::INTERVALS)],
            'amount' => ['required_without:plan_uuid', 'nullable', 'numeric', 'min:1', 'max:1000000'],
            'currency' => ['nullable', 'string', 'size:3'],
            'campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
            'donor_name' => ['nullable', 'string', 'max:255'],
            'donor_email' => ['nullable', 'email', 'max:255'],
            'return_url' => ['nullable', 'url', 'max:2048'],
            'cancel_url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_uuid.exists' => 'الباقة المختارة غير متاحة.',
            'interval.in' => 'دورية الدعم غير صحيحة.',
            'amount.required_without' => 'يجب إدخال المبلغ أو اختيار باقة.',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً.',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر.',
            'donor_email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
        ];
    }
}
