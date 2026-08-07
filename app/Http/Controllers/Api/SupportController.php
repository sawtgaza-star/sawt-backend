<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Major;
use App\Models\TeamMember;
use App\Services\SupportService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * محتوى صفحات «ادعم صوت» العامة — بدون هيدر/فوتر (نفس نمط صفحتَي About و Team).
 */
class SupportController extends Controller
{
    public function __construct(protected SupportService $support) {}

    /** صفحة اختيار طريقة الدعم — الأقسام الثلاثة وبطاقاتها. */
    public function methods(): JsonResponse
    {
        return response()->json(['data' => $this->support->methodsPage()]);
    }

    /** وسائل قسم واحد (electronic | transfer | crypto). */
    public function category(string $category): JsonResponse
    {
        try {
            $payload = $this->support->categoryMethods($category);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'قسم الدعم غير موجود.',
                'error' => 'support_category_not_found',
            ], 404);
        }

        return response()->json(['data' => $payload]);
    }

    /** تفاصيل وسيلة واحدة: بيانات الحساب/الآيبان أو QR المحفظة. */
    public function show(string $uuid): JsonResponse
    {
        try {
            $payload = $this->support->method($uuid);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'وسيلة الدعم غير موجودة أو غير مفعّلة.',
                'error' => 'support_method_not_found',
            ], 404);
        }

        return response()->json(['data' => $payload]);
    }

    /** الباقات والمبالغ الجاهزة (لمرة واحدة / شهري / سنوي). */
    public function plans(): JsonResponse
    {
        return response()->json(['data' => $this->support->plans()]);
    }

    /** تعريف خطوات الويزارد — الفرونت يرسم المؤشر من هنا. */
    public function wizard(): JsonResponse
    {
        return response()->json(['data' => $this->support->wizard()]);
    }

    /**
     * خيارات خطوة «دعم الفريق»: الأقسام وأعضاء الفريق المفعّلون.
     */
    public function teamOptions(): JsonResponse
    {
        $majors = Major::query()->active()->orderBy('sort_order')->get(['uuid', 'name', 'slug']);

        $members = TeamMember::query()
            ->active()
            ->with('major:id,uuid,name')
            ->orderBy('sort_order')
            ->get(['id', 'uuid', 'name', 'role', 'photo', 'major_id']);

        return response()->json(['data' => [
            'majors' => $majors->map(fn (Major $major) => [
                'uuid' => $major->uuid,
                'slug' => $major->slug,
                'name' => $major->getTranslations('name'),
            ])->all(),
            'members' => $members->map(fn (TeamMember $member) => [
                'uuid' => $member->uuid,
                'name' => $member->getTranslations('name'),
                'role' => $member->getTranslations('role'),
                'photo_url' => $member->photo_url,
                'major_uuid' => $member->major?->uuid,
            ])->all(),
        ]]);
    }
}
