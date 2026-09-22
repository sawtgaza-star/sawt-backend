<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Support\StartCheckoutRequest;
use App\Services\SupportCheckoutService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * «ادعم صوت» — تحويل مباشر لـ PayPal (لمرة واحدة / شهري / سنوي).
 *
 *   1) POST /support/checkout          { amount, interval } → { approval_url }
 *   2) الفرونت يعمل window.location = approval_url
 *   3) PayPal يرجع لصفحة الشكر مع ?token=… (لمرة واحدة) أو ?subscription_id=… (دوري)
 *   4) POST /support/checkout/confirm  بنفس الباراميترات → حالة الدفع
 */
class SupportCheckoutController extends Controller
{
    public function __construct(protected SupportCheckoutService $checkout) {}

    public function store(StartCheckoutRequest $request): JsonResponse
    {
        try {
            $result = $this->checkout->start($request->validated(), $request->user());
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'الباقة المختارة غير متاحة.', 'error' => 'support_plan_not_found'], 404);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => 'support_checkout_invalid'], 422);
        } catch (Throwable $e) {
            Log::error('Support checkout failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'تعذّر بدء الدفع عبر PayPal، حاول لاحقاً.', 'error' => 'support_checkout_failed'], 422);
        }

        return response()->json([
            'message' => 'سيتم تحويلك إلى PayPal لإتمام الدعم.',
            'data' => $result,
        ], 201);
    }

    public function confirm(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required_without:subscription_id', 'nullable', 'string', 'max:64'],
            'subscription_id' => ['required_without:token', 'nullable', 'string', 'max:64'],
        ]);

        try {
            $result = $this->checkout->confirm($data);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'عملية الدفع غير موجودة.', 'error' => 'support_checkout_not_found'], 404);
        } catch (Throwable $e) {
            Log::error('Support checkout confirm failed', ['data' => $data, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'تعذّر تأكيد الدفع.', 'error' => 'support_checkout_confirm_failed'], 422);
        }

        $paid = in_array($result['status'], ['completed', 'active'], true);

        return response()->json([
            'message' => $paid ? 'شكراً لدعمك! تم تأكيد الدفع.' : 'لم يكتمل الدفع بعد.',
            'data' => [...$result, 'paid' => $paid],
        ], $paid ? 200 : 202);
    }
}
