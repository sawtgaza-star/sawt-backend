# Support API — «ادعم صوت»

وحدة الدعم تتبع نفس نمط صفحتَي **About** و**Team**: النصوص الثابتة في جدول `settings`
(تبويب «صفحة الدعم» بصفحة الإعدادات)، والبيانات المتكررة (الوسائل، الباقات، الطلبات،
الاشتراكات) لها جداولها وموارد Filament الخاصة. كل الـ endpoints عامة بدون هيدر/فوتر.

الأساس: `/api/v1/support` — كل الردود بصيغة `{"data": …}` أو `{"message": …, "data": …}`.

---

## 1. الأقسام الثلاثة

| القسم | `category` | الوصف |
|---|---|---|
| دفع إلكتروني | `electronic` | PayPal — الدفع فوري والتوثيق آلي (بدون إثبات) |
| تحويل مباشر | `transfer` | بنك / فودافون كاش / انستا باي / كليك / ريفولت — يتطلب إثبات تحويل |
| عملات رقمية | `crypto` | USDT / USDC — عنوان محفظة + صورة QR + شبكة، ويتطلب إثبات |

---

## 2. Endpoints

### المحتوى العام

| الطريقة | المسار | الوصف |
|---|---|---|
| `GET` | `/api/v1/support/methods` | الصفحة الكاملة: هيرو + الأقسام الثلاثة وبطاقاتها + النصوص |
| `GET` | `/api/v1/support/methods/category/{category}` | وسائل قسم واحد + تعريف الويزارد |
| `GET` | `/api/v1/support/methods/{uuid}` | تفاصيل وسيلة: IBAN / رقم المحفظة / QR / التعليمات |
| `GET` | `/api/v1/support/plans` | الباقات مجمّعة بالدورية + إعدادات المبلغ المخصص + إعدادات PayPal |
| `GET` | `/api/v1/support/wizard` | تعريف خطوات الويزارد الأربع (تسمياتها وأيقوناتها من اللوحة) |
| `GET` | `/api/v1/support/team-options` | الأقسام وأعضاء الفريق لخطوة «دعم الفريق» |

### ويزارد الدعم (الضيف مسموح)

| الطريقة | المسار | الخطوة |
|---|---|---|
| `POST` | `/api/v1/support/requests` | **1** — اختيار الوسيلة والمبلغ → يرجع `uuid` |
| `POST` | `/api/v1/support/requests/{uuid}/proof` | **2** — رفع إثبات التحويل (`multipart/form-data`) |
| `POST` | `/api/v1/support/requests/{uuid}/team` | **3** — دعم الفريق |
| `POST` | `/api/v1/support/requests/{uuid}/contact` | **4** — وسيلة التواصل + الإرسال للمراجعة |
| `GET` | `/api/v1/support/requests/{uuid}` | استعراض/استكمال الطلب لاحقاً |
| `POST` | `/api/v1/support/requests/{uuid}/paypal/order` | مسار الدفع الإلكتروني — ينشئ أمر PayPal |

### الاشتراكات الدورية (PayPal Billing)

| الطريقة | المسار | الوصف |
|---|---|---|
| `POST` | `/api/v1/support/subscriptions` | إنشاء اشتراك → يرجع `approval_url` |
| `POST` | `/api/v1/support/subscriptions/{uuid}/activate` | تأكيد بعد موافقة المتبرع |
| `POST` | `/api/v1/support/subscriptions/{uuid}/cancel` | إلغاء |
| `GET` | `/api/v1/support/subscriptions/{uuid}` | حالة الاشتراك |
| `GET` | `/api/v1/support/my-subscriptions` | اشتراكاتي — يتطلب `auth:api` |

> **المعرّفات**: `support_requests` و`support_subscriptions` تستخدم **UUID v4 كامل**
> (وليس المعرّف القصير المستخدم بباقي الموارد) لأنها مفتاح وصول الضيف لطلبه — لا تعرضه علناً.

---

## 3. تدفق الويزارد

```
┌─ 1) اختيار المنصة ─┐   ┌─ 2) إثبات التبرع ─┐   ┌─ 3) دعم الفريق ─┐   ┌─ 4) وسيلة التواصل ─┐
│ POST /requests     │ → │ POST .../proof    │ → │ POST .../team   │ → │ POST .../contact   │
│ status = draft     │   │ رفع لقطات + رقم    │   │ قسم/عضو + رسالة │   │ status = pending   │
└────────────────────┘   └───────────────────┘   └─────────────────┘   └────────────────────┘
                                                                                 ↓
                                                            مراجعة الإدارة من لوحة التحكم
                                                            → approved: يُنشأ تبرع succeeded
```

الطلب يبقى `draft` حتى الخطوة الرابعة، فيقدر المتبرع يقفل الصفحة ويكمل لاحقاً بنفس الـ `uuid`.

**مسار PayPal** يتخطى خطوة الإثبات تلقائياً (`requires_proof = false`)،
ويُعتمد الطلب آلياً بمجرد نجاح التحصيل (عبر `capture` أو الـ webhook).

### مثال — الخطوة 1

```http
POST /api/v1/support/requests
Content-Type: application/json

{ "method_uuid": "rrq0q", "amount": 150, "currency": "USD" }
```

```json
{
  "message": "تم إنشاء طلب الدعم، أكمل الخطوات التالية.",
  "data": {
    "uuid": "70bc3c1f-1740-4b52-a380-02fe3802efb5",
    "status": "draft",
    "amount": 150,
    "wizard": {
      "current_step": 2, "total_steps": 4,
      "current_step_key": "proof", "next_step_key": "team",
      "is_complete": false, "requires_proof": true,
      "progress_percent": 25
    }
  }
}
```

### مثال — الخطوة 2 (رفع الإثبات)

```http
POST /api/v1/support/requests/{uuid}/proof
Content-Type: multipart/form-data

proofs[]            : (ملف) — حتى 5 ملفات، jpg/png/webp/pdf، 5MB للملف
transfer_reference  : TX-12345
transfer_date       : 2026-08-06
sender_name         : محمد
```

الملفات تُخزَّن على **القرص الخاص** (`storage/app/private/support/proofs/{uuid}/`)
ولا تُخدَم برابط عام — التنزيل من اللوحة فقط عبر
`GET /admin/support/proofs/{proof-uuid}` (يتطلب صلاحية `view_any_support::request`).

### مثال — الخطوة 4

```json
{
  "donor_name": "محمد",
  "donor_email": "donor@example.com",
  "donor_phone": "+970599000000",
  "contact_preference": "whatsapp",
  "contact_value": "+970599000000"
}
```

`contact_preference` ∈ `email | whatsapp | phone | none` — واتساب/الهاتف يتطلبان رقماً.

---

## 4. الاشتراك الدوري

```
POST /support/subscriptions            → { approval_url, subscription.uuid }
        ↓ المتبرع يوافق عند PayPal
POST /support/subscriptions/{uuid}/activate  → status = active
        ↓ كل دورة تحصيل
webhook PAYMENT.SALE.COMPLETED         → تُسجَّل كـ Payment مرتبط بالاشتراك
```

الجسم: `{ "plan_uuid": "..." }` أو `{ "interval": "monthly", "amount": 100 }`
مع `subscriber_name` / `subscriber_email` / `return_url` / `cancel_url` اختيارياً.

خطة PayPal تُنشأ تلقائياً عند أول اشتراك بمبلغ/دورية جديدة وتُخزَّن بجدول `settings`
(`paypal_plan_{interval}_{currency}_{amount}`) لإعادة الاستخدام.

**الاشتراك الدوري متاح عبر الدفع الإلكتروني فقط** — التحويل والعملات الرقمية `one_time` حصراً.

### أحداث الـ Webhook المدعومة

`POST /api/v1/paypal/webhook` (موقّع ومُتحقَّق منه) يعالج:

| الحدث | الأثر |
|---|---|
| `PAYMENT.CAPTURE.COMPLETED` | إكمال الدفعة + اعتماد طلب الدعم المرتبط |
| `BILLING.SUBSCRIPTION.ACTIVATED/SUSPENDED/CANCELLED/EXPIRED` | مزامنة حالة الاشتراك |
| `PAYMENT.SALE.COMPLETED` | تسجيل دورة تحصيل جديدة |

---

## 5. الإدارة من لوحة التحكم

| الشاشة | المسار | الوظيفة |
|---|---|---|
| **وسائل الدعم** | `/admin/support-methods` | إضافة بنك/محفظة/عملة رقمية: الاسم، الشعار، IBAN/الرقم، الشبكة، **رفع صورة QR**، حقول إضافية حرة، التعليمات، هل يتطلب إثباتاً |
| **باقات الدعم** | `/admin/support-plans` | المبالغ والدوريات + معرّف خطة PayPal |
| **طلبات الدعم** | `/admin/support-requests` | مراجعة الإثباتات، **اعتماد** (يُنشئ تبرعاً موثّقاً ويزيد رصيد الحملة) أو **رفض** بسبب |
| **اشتراكات الدعم** | `/admin/support-subscriptions` | عرض، مزامنة مع PayPal، إلغاء |
| **الإعدادات → صفحة الدعم** | `/admin/settings` | الهيرو، نصوص البطاقات الثلاث، تسميات الخطوات، المبالغ، نصوص الأزرار والرسائل |

جميع الحقول النصية ثنائية اللغة — الـ API يرجع `{"ar": "...", "en": "..."}` والفرونت يختار اللغة.

---

## 6. الملفات

```
routes/api/v1.php                                    prefix('support')
app/Http/Controllers/Api/SupportController.php        المحتوى العام
app/Http/Controllers/Api/SupportRequestController.php الويزارد
app/Http/Controllers/Api/SupportSubscriptionController.php
app/Http/Controllers/SupportProofController.php       تنزيل الإثبات (لوحة فقط)
app/Http/Requests/Api/Support/*.php                   5 form requests برسائل عربية
app/Http/Resources/Support*Resource.php               3 موارد API
app/Services/SupportService.php                       محتوى الصفحات
app/Services/SupportRequestService.php                محرّك الويزارد + الاعتماد/الرفض
app/Services/SupportSubscriptionService.php           اشتراكات PayPal
app/Services/PayPalService.php                        + createProduct/createPlan/createSubscription/…
app/Repositories/SupportRepository.php                (+ Contracts/SupportRepositoryInterface)
app/Support/SupportOptions.php                        مصدر واحد لقوائم الخيارات
app/Models/Support{Method,Plan,Request,RequestProof,Subscription}.php
app/Filament/Resources/Support*Resource.php           4 موارد لوحة + صفحاتها
app/Policies/Support*Policy.php                       صلاحيات Shield
database/migrations/2026_08_07_120000_create_support_tables.php
database/seeders/SupportSeeder.php                    9 وسائل + 12 باقة + نصوص الصفحة
```

## 7. التشغيل

```bash
php artisan migrate
php artisan db:seed --class=SupportSeeder
php artisan shield:generate --all --panel=admin --option=permissions   # صلاحيات الموارد الجديدة
```

بعدها من اللوحة: **المالية → وسائل الدعم** لتعبئة أرقام الحسابات والآيبان ورفع صور QR والشعارات.
