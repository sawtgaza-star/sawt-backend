<?php

namespace App\Http\Requests\Api\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * الخطوة 2 — رفع لقطات إثبات التحويل مع بيانات العملية.
 */
class StoreProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proofs' => ['nullable', 'array', 'max:5'],
            'proofs.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'transfer_reference' => ['nullable', 'string', 'max:255'],
            'transfer_date' => ['nullable', 'date', 'before_or_equal:today'],
            'sender_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'proofs.max' => 'يمكن رفع 5 صور كحد أقصى.',
            'proofs.*.file' => 'الملف المرفوع غير صالح.',
            'proofs.*.mimes' => 'الصيغ المسموحة: JPG أو PNG أو WEBP أو PDF.',
            'proofs.*.max' => 'حجم الملف يجب ألا يتجاوز 5 ميجابايت.',
            'transfer_date.before_or_equal' => 'تاريخ التحويل لا يمكن أن يكون في المستقبل.',
        ];
    }

    /**
     * @return array<int, UploadedFile>
     */
    public function proofFiles(): array
    {
        return array_values(array_filter((array) $this->file('proofs', [])));
    }
}
