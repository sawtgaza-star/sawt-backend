<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Body for POST /api/v1/pages/courses/{slug}/subscribe (guest 3-step modal, one submit).
 *
 * Step 1 — all required: full_name, phone, email
 * Step 2 — first two required: academic_level, attended_similar_course; goals_interests optional
 * Step 3 — join_goal required; additional_notes optional
 */
class StoreCourseSubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Step 1 — المعلومات الشخصية
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'phone_country_code' => ['nullable', 'string', 'max:10'],
            'email' => ['required', 'email', 'max:255'],

            // Step 2 — البيانات الأكاديمية والمهنية
            'academic_level' => ['required', 'string', 'max:255'],
            'attended_similar_course' => ['required', 'boolean'],
            'goals_interests' => ['nullable', 'string', 'max:2000'],

            // Step 3 — أهدافك واهتماماتك
            'join_goal' => ['required', 'string', 'max:255'],
            'additional_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'الاسم الكامل مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'academic_level.required' => 'المستوى الدراسي أو المهني مطلوب.',
            'attended_similar_course.required' => 'يرجى تحديد إن كنت قد التحقت بدورة مشابهة.',
            'join_goal.required' => 'هدف الالتحاق بالدورة مطلوب.',
        ];
    }

    /**
     * Normalize yes/no and checkbox-style payloads to boolean.
     */
    protected function prepareForValidation(): void
    {
        if ($this->exists('attended_similar_course')) {
            $raw = $this->input('attended_similar_course');
            if (is_string($raw)) {
                $normalized = match (strtolower(trim($raw))) {
                    '1', 'true', 'yes', 'نعم' => true,
                    '0', 'false', 'no', 'لا' => false,
                    default => $raw,
                };
                $this->merge(['attended_similar_course' => $normalized]);
            }
        }
    }
}
