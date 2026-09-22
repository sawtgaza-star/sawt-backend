{{-- Collaboration → accepted --}}
@php
    /** @var string $name */
    /** @var string $typeLabel */
    /** @var string $requestUuid */
@endphp

@component('emails.layout', [
    'title' => 'تم قبول طلب التعاون',
    'heading' => 'مرحباً '.$name,
    'subheading' => 'تم قبول طلب التعاون الخاص بكم',
    'headerColor' => '#0F3D2E',
    'headerIcon' => '✓',
])
    <p style="margin:0 0 16px;">يسعدنا إخباركم بأنه تم قبول طلب التعاون الخاص بكم ({{ $typeLabel }}).</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        @include('emails.partials.info-row', ['label' => 'نوع التعاون', 'value' => $typeLabel])
        <tr><td style="height:10px;"></td></tr>
        @include('emails.partials.info-row', ['label' => 'رقم الطلب', 'value' => $requestUuid])
    </table>

    <p style="margin:0 0 16px;">سيتواصل معكم فريق منصة صوت قريباً لمتابعة الخطوات التالية.</p>
    <p style="margin:0;color:#6b7280;font-size:13px;">شكراً لاهتمامكم بالتعاون مع منصة صوت.</p>
@endcomponent
