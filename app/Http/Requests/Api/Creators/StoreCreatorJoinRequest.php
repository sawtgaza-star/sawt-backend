<?php

namespace App\Http\Requests\Api\Creators;

use App\Models\CreatorJoinRequest;
use App\Services\CreatorJoinRequestService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreCreatorJoinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => Str::lower(trim((string) $this->input('email'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'email' => ['required', 'email', 'max:255'],
            'content_types' => ['required', 'array', 'min:1'],
            'content_types.*' => ['required', 'string', 'max:80'],
            'followers_count' => ['required', 'integer', 'min:0'],
            'content_bio' => ['required', 'string', 'max:5000'],
            'socials' => ['required', 'array', 'min:1'],
            'socials.*.platform' => ['required', 'string', Rule::in(CreatorJoinRequest::PLATFORMS)],
            'socials.*.url' => ['required', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $email = $this->input('email');

            if (! $email || $validator->errors()->has('email')) {
                return;
            }

            try {
                app(CreatorJoinRequestService::class)->assertEmailCanJoin((string) $email);
            } catch (ValidationException $e) {
                $validator->errors()->add('email', $e->errors()['email'][0] ?? 'هذا البريد مسجّل مسبقاً كصانع محتوى.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'الاسم الكامل مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'content_types.required' => 'اختر نوع محتوى واحداً على الأقل.',
            'followers_count.required' => 'عدد المتابعين مطلوب.',
            'content_bio.required' => 'نبذة المحتوى مطلوبة.',
            'socials.required' => 'أضف رابط تواصل واحداً على الأقل.',
            'socials.*.url.url' => 'رابط المنصة غير صحيح.',
        ];
    }
}
