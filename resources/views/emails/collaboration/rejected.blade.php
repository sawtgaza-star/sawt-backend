{{-- Collaboration → rejected --}}
@php
    /** @var string $name */
    /** @var string $typeLabel */
    /** @var string $requestUuid */
    /** @var string|null $reason */
@endphp

@component('emails.layout', [
    'title' => 'بخصوص طلب التعاون',
    'heading' => 'مرحباً '.$name,
    'subheading' => 'تحديث بخصوص طلب التعاون',
    'headerColor' => '#7F1D1D',
    'headerIcon' => '!',
])
    <p style="margin:0 0 16px;">نشكركم على اهتمامكم بالتعاون مع منصة صوت ({{ $typeLabel }}).</p>
    <p style="margin:0 0 16px;">نأسف لإبلاغكم بأنه تعذّر قبول طلب التعاون في الوقت الحالي.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        @include('emails.partials.info-row', ['label' => 'رقم الطلب', 'value' => $requestUuid])
        @if ($reason)
            <tr><td style="height:10px;"></td></tr>
            @include('emails.partials.info-row', ['label' => 'السبب', 'value' => $reason, 'danger' => true])
        @endif
    </table>

    <p style="margin:0;color:#6b7280;font-size:13px;">يمكنكم تقديم طلب جديد لاحقاً. شكراً لتفهمكم.</p>
@endcomponent
