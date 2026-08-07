<?php

namespace App\Http\Requests\Api\Support;

use Illuminate\Foundation\Http\FormRequest;

/**
 * الخطوة 3 — دعم الفريق: توجيه الدعم لقسم أو عضو معيّن مع رسالة اختيارية.
 */
class StoreTeamStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'major_uuid' => ['nullable', 'string', 'exists:majors,uuid'],
            'team_member_uuid' => ['nullable', 'string', 'exists:team_members,uuid'],
            'message' => ['nullable', 'string', 'max:2000'],
            'is_anonymous' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'major_uuid.exists' => 'القسم المختار غير موجود.',
            'team_member_uuid.exists' => 'عضو الفريق المختار غير موجود.',
            'message.max' => 'الرسالة يجب ألا تتجاوز 2000 حرف.',
        ];
    }
}
