<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Support\StartSupportRequest;
use App\Http\Requests\Api\Support\StoreContactStepRequest;
use App\Http\Requests\Api\Support\StoreProofRequest;
use App\Http\Requests\Api\Support\StoreTeamStepRequest;
use App\Http\Resources\SupportRequestResource;
use App\Models\Major;
use App\Models\SupportRequest;
use App\Models\TeamMember;
use App\Repositories\Contracts\SupportRepositoryInterface;
use App\Services\SupportRequestService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * ويزارد الدعم بأربع خطوات. الطلب يُنشأ بالخطوة الأولى ويُعاد الـ uuid،
 * ثم يستكمل المتبرع باقي الخطوات بنفس المعرّف (يقدر يقفل ويرجع لاحقاً).
 *
 * الـ uuid هنا UUID v4 كامل ويُعامَل كمفتاح وصول للطلب — لا تعرضه إلا لصاحبه.
 */
class SupportRequestController extends Controller
{
    public function __construct(
        protected SupportRequestService $wizard,
        protected SupportRepositoryInterface $support,
    ) {}

    /** الخطوة 1 — اختيار الوسيلة والمبلغ. */
    public function store(StartSupportRequest $request): JsonResponse
    {
        try {
            $supportRequest = $this->wizard->start($request->validated(), $request->user());
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'وسيلة الدعم المختارة غير متاحة.',
                'error' => 'support_method_not_found',
            ], 404);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => 'support_request_invalid'], 422);
        }

        return response()->json([
            'message' => 'تم إنشاء طلب الدعم، أكمل الخطوات التالية.',
            'data' => new SupportRequestResource($supportRequest->load(['method', 'plan'])),
        ], 201);
    }

    /** استعراض/استكمال طلب قائم. */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $supportRequest = $this->resolve($uuid);

        if (! $supportRequest) {
            return $this->notFound();
        }

        if ($denied = $this->guardOwnership($request, $supportRequest)) {
            return $denied;
        }

        return response()->json([
            'data' => new SupportRequestResource($supportRequest),
        ]);
    }

    /** الخطوة 2 — رفع إثبات التحويل (multipart/form-data). */
    public function storeProof(StoreProofRequest $request, string $uuid): JsonResponse
    {
        $supportRequest = $this->resolve($uuid);

        if (! $supportRequest) {
            return $this->notFound();
        }

        if ($denied = $this->guardOwnership($request, $supportRequest)) {
            return $denied;
        }

        try {
            $supportRequest = $this->wizard->saveProofStep(
                $supportRequest,
                $request->proofFiles(),
                $request->validated(),
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => 'support_proof_invalid'], 422);
        } catch (Throwable $e) {
            Log::error('Support proof upload failed', ['uuid' => $uuid, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'تعذّر حفظ الإثبات، حاول مجدداً.', 'error' => 'support_proof_failed'], 500);
        }

        return response()->json([
            'message' => 'تم حفظ إثبات التحويل.',
            'data' => new SupportRequestResource($supportRequest),
        ]);
    }

    /** الخطوة 3 — دعم الفريق. */
    public function storeTeamStep(StoreTeamStepRequest $request, string $uuid): JsonResponse
    {
        $supportRequest = $this->resolve($uuid);

        if (! $supportRequest) {
            return $this->notFound();
        }

        if ($denied = $this->guardOwnership($request, $supportRequest)) {
            return $denied;
        }

        $data = $request->validated();

        try {
            $supportRequest = $this->wizard->saveTeamStep($supportRequest, [
                'major_id' => filled($data['major_uuid'] ?? null)
                    ? Major::where('uuid', $data['major_uuid'])->value('id')
                    : null,
                'team_member_id' => filled($data['team_member_uuid'] ?? null)
                    ? TeamMember::where('uuid', $data['team_member_uuid'])->value('id')
                    : null,
                'message' => $data['message'] ?? null,
                'is_anonymous' => $data['is_anonymous'] ?? false,
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => 'support_step_invalid'], 422);
        }

        return response()->json([
            'message' => 'تم حفظ بيانات دعم الفريق.',
            'data' => new SupportRequestResource($supportRequest),
        ]);
    }

    /** الخطوة 4 — وسيلة التواصل + إرسال الطلب للمراجعة. */
    public function storeContactStep(StoreContactStepRequest $request, string $uuid): JsonResponse
    {
        $supportRequest = $this->resolve($uuid);

        if (! $supportRequest) {
            return $this->notFound();
        }

        if ($denied = $this->guardOwnership($request, $supportRequest)) {
            return $denied;
        }

        try {
            $supportRequest = $this->wizard->saveContactStep($supportRequest, $request->validated());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => 'support_step_invalid'], 422);
        }

        return response()->json([
            'message' => 'تم إرسال طلب الدعم بنجاح، سيتم مراجعته والتواصل معك.',
            'data' => new SupportRequestResource($supportRequest),
        ]);
    }

    /**
     * مسار الدفع الإلكتروني — إنشاء أمر PayPal مربوط بهذا الطلب.
     * الرد متوافق مع Smart Buttons: { id: "<order id>" }.
     */
    public function createPayPalOrder(Request $request, string $uuid): JsonResponse
    {
        $supportRequest = $this->resolve($uuid);

        if (! $supportRequest) {
            return $this->notFound();
        }

        if ($denied = $this->guardOwnership($request, $supportRequest)) {
            return $denied;
        }

        try {
            $result = $this->wizard->startPayPalOrder($supportRequest);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => 'support_paypal_invalid'], 422);
        } catch (Throwable $e) {
            Log::error('Support PayPal order failed', ['uuid' => $uuid, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'تعذّر إنشاء أمر الدفع.', 'error' => 'support_paypal_failed'], 422);
        }

        return response()->json([
            'id' => $result['order_id'],
            'payment' => $result['payment']->uuid,
        ]);
    }

    protected function resolve(string $uuid): ?SupportRequest
    {
        return $this->support->findRequestByUuid($uuid);
    }

    /**
     * الطلب المرتبط بحساب لا يجوز لمستخدم آخر مسجّل الدخول تعديله،
     * حتى لو عرف الـ uuid.
     */
    protected function guardOwnership(Request $request, SupportRequest $supportRequest): ?JsonResponse
    {
        $user = $request->user();

        if ($supportRequest->user_id && $user && $user->id !== $supportRequest->user_id) {
            return response()->json([
                'message' => 'لا تملك صلاحية الوصول لهذا الطلب.',
                'error' => 'support_request_forbidden',
            ], 403);
        }

        return null;
    }

    protected function notFound(): JsonResponse
    {
        return response()->json([
            'message' => 'طلب الدعم غير موجود.',
            'error' => 'support_request_not_found',
        ], 404);
    }
}
