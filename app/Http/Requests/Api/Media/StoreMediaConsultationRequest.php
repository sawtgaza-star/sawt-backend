<?php

namespace App\Http\Requests\Api\Media;

use App\Models\MediaServiceItem;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

/**
 * Validates «احجز استشارتك» payload from the media landing form.
 * `service` = active media service slug or uuid.
 */
class StoreMediaConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('email')) {
            $merge['email'] = Str::lower(trim((string) $this->input('email')));
        }

        if ($this->has('name')) {
            $merge['name'] = trim((string) $this->input('name'));
        }

        if ($this->has('phone')) {
            $merge['phone'] = trim((string) preg_replace('/[^\d+\s\-]/', '', (string) $this->input('phone')));
        }

        if ($this->has('country_code')) {
            $code = trim((string) $this->input('country_code'));
            if ($code !== '' && ! str_starts_with($code, '+')) {
                $code = '+'.$code;
            }
            $merge['country_code'] = $code !== '' ? $code : null;
        }

        if ($this->has('service')) {
            $merge['service'] = Str::lower(trim((string) $this->input('service')));
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email:filter', 'max:255'],
            'phone' => ['required', 'string', 'min:6', 'max:40'],
            'country_code' => ['nullable', 'string', 'max:8', 'regex:/^\+?\d{1,7}$/'],
            'service' => ['required', 'string', 'max:120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        // Confirm the dropdown value maps to an active media service
        $validator->after(function (Validator $validator): void {
            $key = (string) $this->input('service', '');
            if ($key === '' || $validator->errors()->has('service')) {
                return;
            }

            $exists = MediaServiceItem::query()
                ->where('is_active', true)
                ->where(function ($q) use ($key) {
                    $q->where('slug', $key)->orWhere('uuid', $key);
                })
                ->exists();

            if (! $exists) {
                $validator->errors()->add('service', 'الخدمة المختارة غير متاحة.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم الكامل',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'country_code' => 'رمز الدولة',
            'service' => 'الخدمة المطلوبة',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الرجاء إدخال الاسم الكامل.',
            'name.min' => 'الاسم قصير جداً.',
            'email.required' => 'الرجاء إدخال البريد الإلكتروني.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'phone.required' => 'الرجاء إدخال رقم الهاتف.',
            'phone.min' => 'رقم الهاتف قصير جداً.',
            'country_code.regex' => 'رمز الدولة غير صحيح (مثال: +970).',
            'service.required' => 'الرجاء اختيار الخدمة المطلوبة.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first() ?: 'يرجى التحقق من بيانات طلب الاستشارة.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
