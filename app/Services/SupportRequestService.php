<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Payment;
use App\Models\SupportMethod;
use App\Models\SupportRequest;
use App\Models\SupportRequestProof;
use App\Models\User;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\SupportRepositoryInterface;
use App\Support\SupportOptions;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * محرّك ويزارد الدعم: كل خطوة تتحفظ لحالها والطلب يبقى draft لحد الخطوة الأخيرة،
 * فالمتبرع يقدر يقفل الصفحة ويكمل بعدين بنفس الـ uuid.
 *
 * الترتيب: 1) الوسيلة  2) إثبات التحويل  3) دعم الفريق  4) وسيلة التواصل
 */
class SupportRequestService
{
    /** قرص تخزين الإثباتات — خاص لأنها بيانات مالية. */
    public const PROOF_DISK = 'local';

    public const PROOF_DIRECTORY = 'support/proofs';

    public function __construct(
        protected SupportRepositoryInterface $support,
        protected SettingRepositoryInterface $settings,
        protected CheckoutService $checkout,
    ) {}

    /**
     * الخطوة 1 — اختيار الوسيلة والمبلغ. تنشئ الطلب وترجعه.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException|RuntimeException
     */
    public function start(array $data, ?User $user = null): SupportRequest
    {
        $method = $this->support->findActiveMethodByUuid((string) $data['method_uuid']);

        if (! $method) {
            throw (new ModelNotFoundException)->setModel(SupportMethod::class, [$data['method_uuid']]);
        }

        $plan = filled($data['plan_uuid'] ?? null)
            ? $this->support->findActivePlanByUuid((string) $data['plan_uuid'])
            : null;

        $interval = $plan?->interval ?? ($data['interval'] ?? 'one_time');
        $amount = (float) ($plan?->amount ?? ($data['amount'] ?? 0));
        $currency = strtoupper((string) ($plan?->currency ?? $data['currency'] ?? $this->defaultCurrency()));

        $this->assertAmount($amount);

        // التحويل والعملات الرقمية لا يدعمان الاشتراك الدوري — الدوري عبر PayPal فقط
        if ($interval !== 'one_time' && $method->category !== 'electronic') {
            throw new RuntimeException('الاشتراك الدوري متاح عبر الدفع الإلكتروني فقط.');
        }

        $request = SupportRequest::create([
            'user_id' => $user?->id,
            'support_method_id' => $method->id,
            'support_plan_id' => $plan?->id,
            'campaign_id' => $data['campaign_id'] ?? null,
            'category' => $method->category,
            'interval' => $interval,
            'amount' => $amount,
            'currency' => $currency,
            'donor_name' => $data['donor_name'] ?? $user?->name,
            'donor_email' => $data['donor_email'] ?? $user?->email,
            'status' => 'draft',
            'current_step' => 2,
        ]);

        return $request->fresh(['method', 'plan']);
    }

    /**
     * الخطوة 2 — إثبات التحويل: صور + رقم عملية + تاريخ.
     * وسائل الدفع الإلكتروني (PayPal) لا تحتاج إثباتاً — تُعتمد آلياً بعد نجاح الدفع.
     *
     * @param  array<int, UploadedFile>  $files
     * @param  array<string, mixed>  $data
     */
    public function saveProofStep(SupportRequest $request, array $files, array $data = []): SupportRequest
    {
        $this->assertEditable($request);

        if ($request->needsProof() && $files === [] && $request->proofs()->count() === 0) {
            throw new RuntimeException('يجب رفع صورة إثبات التحويل.');
        }

        return DB::transaction(function () use ($request, $files, $data) {
            foreach ($files as $file) {
                $this->storeProof($request, $file);
            }

            $request->fill(array_filter([
                'transfer_reference' => $data['transfer_reference'] ?? null,
                'transfer_date' => $data['transfer_date'] ?? null,
                'sender_name' => $data['sender_name'] ?? null,
            ], fn ($value) => $value !== null));

            $request->current_step = max($request->current_step, 3);
            $request->save();

            return $request->fresh(['method', 'proofs']);
        });
    }

    /**
     * الخطوة 3 — دعم الفريق: القسم/العضو الذي يذهب له الدعم + رسالة.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveTeamStep(SupportRequest $request, array $data): SupportRequest
    {
        $this->assertEditable($request);

        $request->fill([
            'major_id' => $data['major_id'] ?? null,
            'team_member_id' => $data['team_member_id'] ?? null,
            'message' => $data['message'] ?? null,
            'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
        ]);

        $request->current_step = max($request->current_step, 4);
        $request->save();

        return $request->fresh(['method', 'proofs', 'major', 'teamMember']);
    }

    /**
     * الخطوة 4 — وسيلة التواصل، وبها يُرسَل الطلب للمراجعة.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveContactStep(SupportRequest $request, array $data): SupportRequest
    {
        $this->assertEditable($request);

        if ($request->needsProof() && $request->proofs()->count() === 0) {
            throw new RuntimeException('لا يمكن إرسال الطلب قبل رفع إثبات التحويل.');
        }

        $request->fill([
            'donor_name' => $data['donor_name'] ?? $request->donor_name,
            'donor_email' => $data['donor_email'] ?? $request->donor_email,
            'donor_phone' => $data['donor_phone'] ?? $request->donor_phone,
            'contact_preference' => $data['contact_preference'] ?? 'email',
            'contact_value' => $data['contact_value'] ?? $data['donor_email'] ?? $request->donor_email,
            'subscribe_newsletter' => (bool) ($data['subscribe_newsletter'] ?? false),
            'status' => 'pending',
            'current_step' => 4,
            'submitted_at' => now(),
        ]);

        $request->save();

        return $request->fresh(['method', 'proofs', 'major', 'teamMember']);
    }

    /**
     * مسار الدفع الإلكتروني: ينشئ تبرعاً وأمر PayPal مربوطين بهذا الطلب.
     *
     * @return array{order_id: string, payment: Payment}
     */
    public function startPayPalOrder(SupportRequest $request): array
    {
        if ($request->category !== 'electronic') {
            throw new RuntimeException('هذه الوسيلة لا تدعم الدفع عبر PayPal.');
        }

        $donation = $request->donation ?: Donation::create([
            'campaign_id' => $request->campaign_id,
            'user_id' => $request->user_id,
            'donor_name' => $request->donor_name,
            'donor_email' => $request->donor_email,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'payment_method' => 'paypal',
            'status' => 'pending',
        ]);

        $request->update(['donation_id' => $donation->id]);

        return $this->checkout->startOrderFor(
            $donation,
            $request->user_id,
            (float) $request->amount,
            $request->currency,
            "support:{$request->uuid}",
            'Support Sawt',
        );
    }

    /**
     * اعتماد الطلب من اللوحة — يوثّق التبرع ويربطه بالحملة إن وُجدت.
     */
    public function approve(SupportRequest $request, ?User $admin = null, ?string $note = null): SupportRequest
    {
        if ($request->isDraft()) {
            throw new RuntimeException('لا يمكن اعتماد طلب غير مكتمل.');
        }

        return DB::transaction(function () use ($request, $admin, $note) {
            $donation = $request->donation ?: Donation::create([
                'campaign_id' => $request->campaign_id,
                'user_id' => $request->user_id,
                'donor_name' => $request->is_anonymous ? null : $request->donor_name,
                'donor_email' => $request->donor_email,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'payment_method' => SupportOptions::donationPaymentMethod(
                    $request->category,
                    $request->method?->provider,
                ),
                'status' => 'pending',
            ]);

            if ($donation->status !== 'succeeded') {
                $donation->update(['status' => 'succeeded']);

                if ($donation->campaign_id) {
                    $donation->campaign()->increment('current_amount', $donation->amount);
                }
            }

            $request->update([
                'donation_id' => $donation->id,
                'status' => 'approved',
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now(),
                'admin_note' => $note ?? $request->admin_note,
                'rejection_reason' => null,
            ]);

            return $request->fresh(['donation', 'method']);
        });
    }

    public function reject(SupportRequest $request, ?User $admin = null, ?string $reason = null): SupportRequest
    {
        $request->update([
            'status' => 'rejected',
            'reviewed_by' => $admin?->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $request->fresh();
    }

    protected function storeProof(SupportRequest $request, UploadedFile $file): SupportRequestProof
    {
        $path = Storage::disk(self::PROOF_DISK)->putFile(
            self::PROOF_DIRECTORY.'/'.$request->uuid,
            $file,
        );

        if ($path === false) {
            throw new RuntimeException('تعذّر حفظ صورة الإثبات.');
        }

        return $request->proofs()->create([
            'path' => $path,
            'disk' => self::PROOF_DISK,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    protected function assertEditable(SupportRequest $request): void
    {
        if ($request->status === 'approved' || $request->status === 'rejected') {
            throw new RuntimeException('تمت مراجعة هذا الطلب ولا يمكن تعديله.');
        }
    }

    protected function assertAmount(float $amount): void
    {
        $min = (float) ($this->settings->get('support_min_amount', $this->settings->get('min_donation_amount', 5)) ?: 5);

        if ($amount < $min) {
            throw new RuntimeException("الحد الأدنى للدعم هو {$min}.");
        }
    }

    protected function defaultCurrency(): string
    {
        return (string) ($this->settings->get('support_default_currency', 'USD') ?: 'USD');
    }
}
