{{-- Media consultation → rejected --}}
@php
    /** @var string $name */
    /** @var string $service */
    /** @var string $requestUuid */
    /** @var string|null $reason */
@endphp

@component('emails.layout', [
    'title' => 'بخصوص طلب الاستشارة',
    'heading' => 'مرحباً '.$name,
    'subheading' => 'تحديث بخصوص طلب حجز الاستشارة',
    'headerColor' => '#7F1D1D',
    'headerIcon' => '!',
])
    <p style="margin:0 0 16px;">نشكرك على اهتمامك بصوت ميديا.</p>
    <p style="margin:0 0 16px;">نأسف لإبلاغك بأنه تعذّر قبول طلب حجز الاستشارة في الوقت الحالي.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        @include('emails.partials.info-row', ['label' => 'الخدمة', 'value' => $service])
        <tr><td style="height:10px;"></td></tr>
        @include('emails.partials.info-row', ['label' => 'رقم الطلب', 'value' => $requestUuid])
        @if ($reason)
            <tr><td style="height:10px;"></td></tr>
            @include('emails.partials.info-row', ['label' => 'السبب', 'value' => $reason, 'danger' => true])
        @endif
    </table>

    <p style="margin:0;color:#6b7280;font-size:13px;">يمكنك التواصل معنا لاحقاً أو تقديم طلب جديد. نتمنى لك التوفيق.</p>
@endcomponent
