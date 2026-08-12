<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Support\CreateSubscriptionRequest;
use App\Http\Resources\SupportSubscriptionResource;
use App\Models\SupportSubscription;
use App\Services\SupportSubscriptionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * اشتراكات الدعم الدورية عبر PayPal Billing.
 *
 * التدفق بالفرونت:
 *   1) POST /support/subscriptions          → يرجع approval_url + uuid
 *   2) المتبرع يوافق عند PayPal
 *   3) POST /support/subscriptions/{uuid}/activate  → تأكيد الحالة
 */
class SupportSubscriptionController extends Controller
{
    public function __construct(protected SupportSubscriptionService $subscriptions) {}

    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        try {
            $result = $this->subscriptions->create($request->validated(), $request->user());
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'الباقة المختارة غير متاحة.',
                'error' => 'support_plan_not_found',
            ], 404);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => 'support_subscription_invalid'], 422);
        } catch (Throwable $e) {
            Log::error('Support subscription creation failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'تعذّر إنشاء الاشتراك، حاول لاحقاً.', 'error' => 'support_subscription_failed'], 422);
        }

        return response()->json([
            'message' => 'تم إنشاء الاشتراك، أكمل الموافقة عبر PayPal.',
            'data' => [
                'subscription' => new SupportSubscriptionResource($result['subscription']),
                'approval_url' => $result['approval_url'],
            ],
        ], 201);
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        $subscription = $this->resolve($uuid);

        if (! $subscription) {
            return $this->notFound();
        }

        if ($denied = $this->guardOwnership($request, $subscription)) {
            return $denied;
        }

        return response()->json(['data' => new SupportSubscriptionResource($subscription)]);
    }

    /** تأكيد الاشتراك بعد موافقة المتبرع عند PayPal. */
    public function activate(Request $request, string $uuid): JsonResponse
    {
        $subscription = $this->resolve($uuid);

        if (! $subscription) {
            return $this->notFound();
        }

        if ($denied = $this->guardOwnership($request, $subscription)) {
            return $denied;
        }

        try {
            $subscription = $this->subscriptions->activate($subscription);
        } catch (Throwable $e) {
            Log::error('Support subscription activation failed', ['uuid' => $uuid, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'تعذّر تأكيد الاشتراك.', 'error' => 'support_subscription_activate_failed'], 422);
        }

        return response()->json([
            'message' => 'تم تفعيل الاشتراك.',
            'data' => new SupportSubscriptionResource($subscription),
        ]);
    }

    public function cancel(Request $request, string $uuid): JsonResponse
    {
        $subscription = $this->resolve($uuid);

        if (! $subscription) {
            return $this->notFound();
        }

        if ($denied = $this->guardOwnership($request, $subscription)) {
            return $denied;
        }

        try {
            $subscription = $this->subscriptions->cancel(
                $subscription,
                (string) ($request->input('reason') ?: 'Cancelled by donor'),
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => 'support_subscription_invalid'], 422);
        }

        return response()->json([
            'message' => 'تم إلغاء الاشتراك.',
            'data' => new SupportSubscriptionResource($subscription),
        ]);
    }

    /** اشتراكات المستخدم المسجّل. */
    public function mine(Request $request): JsonResponse
    {
        $subscriptions = SupportSubscription::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => SupportSubscriptionResource::collection($subscriptions),
        ]);
    }

    protected function resolve(string $uuid): ?SupportSubscription
    {
        return SupportSubscription::where('uuid', $uuid)->first();
    }

    protected function guardOwnership(Request $request, SupportSubscription $subscription): ?JsonResponse
    {
        $user = $request->user();

        if ($subscription->user_id && $user && $user->id !== $subscription->user_id) {
            return response()->json([
                'message' => 'لا تملك صلاحية الوصول لهذا الاشتراك.',
                'error' => 'support_subscription_forbidden',
            ], 403);
        }

        return null;
    }

    protected function notFound(): JsonResponse
    {
        return response()->json([
            'message' => 'الاشتراك غير موجود.',
            'error' => 'support_subscription_not_found',
        ], 404);
    }
}
