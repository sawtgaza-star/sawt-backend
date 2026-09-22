{{-- Creators → accepted --}}
@php
    /** @var string $name */
    /** @var string|null $email */
    /** @var string|null $temporaryPassword */
    /** @var string $loginUrl */
@endphp

@component('emails.layout', [
    'title' => 'تم قبول طلبك كصانع محتوى',
    'heading' => 'مرحباً '.$name,
    'subheading' => 'تم قبول طلب انضمامك كصانع محتوى في منصة صوت',
    'headerColor' => '#0F3D2E',
    'headerIcon' => '✓',
])
    <p style="margin:0 0 16px;">يسعدنا إخبارك بأنه تم قبول طلب انضمامك كصانع محتوى في منصة صوت.</p>
    <p style="margin:0 0 16px;">يمكنك الآن تسجيل الدخول إلى حسابك.</p>

    @if ($temporaryPassword)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
            @include('emails.partials.info-row', ['label' => 'البريد الإلكتروني', 'value' => $email])
            <tr><td style="height:10px;"></td></tr>
            @include('emails.partials.info-row', ['label' => 'كلمة المرور المؤقتة', 'value' => $temporaryPassword])
        </table>
        <p style="margin:0 0 22px;color:#6b7280;font-size:13px;">يُفضَّل تغيير كلمة المرور بعد أول تسجيل دخول.</p>
    @else
        <p style="margin:0 0 22px;">استخدم البريد الإلكتروني وكلمة المرور الحاليين لتسجيل الدخول.</p>
    @endif

    @include('emails.partials.button', ['url' => $loginUrl, 'label' => 'تسجيل الدخول'])

    <p style="margin:24px 0 0;color:#6b7280;font-size:13px;">شكراً لانضمامك إلى منصة صوت.</p>
@endcomponent
