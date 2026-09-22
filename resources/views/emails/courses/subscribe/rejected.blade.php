{{-- Courses → subscribe → rejected --}}
@php
    /** @var string $name */
    /** @var string $courseTitle */
    /** @var string|null $reason */
    /** @var string $incubatorUrl */
@endphp

@component('emails.layout', [
    'title' => 'بخصوص طلب الاشتراك',
    'heading' => 'مرحباً '.$name,
    'subheading' => 'تحديث بخصوص طلب اشتراكك في الدورة',
    'headerColor' => '#7F1D1D',
    'headerIcon' => '!',
])
    <p style="margin:0 0 16px;">نشكرك على اهتمامك بحاضنة صوت.</p>
    <p style="margin:0 0 16px;">نأسف لإبلاغك بأنه تعذّر قبول طلب اشتراكك في الدورة في الوقت الحالي.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        @include('emails.partials.info-row', ['label' => 'اسم الدورة', 'value' => $courseTitle])
        @if ($reason)
            <tr><td style="height:10px;"></td></tr>
            @include('emails.partials.info-row', ['label' => 'السبب', 'value' => $reason, 'danger' => true])
        @endif
    </table>

    <p style="margin:0 0 22px;">يمكنك تصفح دورات أخرى أو تقديم طلب جديد لاحقاً — نتمنى لك التوفيق.</p>

    @include('emails.partials.button', ['url' => $incubatorUrl, 'label' => 'تصفح كورسات ثانية'])

    <p style="margin:24px 0 0;color:#6b7280;font-size:13px;">فريق صوت</p>
@endcomponent
