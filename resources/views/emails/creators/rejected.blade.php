{{-- Creators → rejected --}}
@php
    /** @var string $name */
    /** @var string|null $reason */
@endphp

@component('emails.layout', [
    'title' => 'بخصوص طلب صانع المحتوى',
    'heading' => 'مرحباً '.$name,
    'subheading' => 'تحديث بخصوص طلب انضمامك كصانع محتوى',
    'headerColor' => '#7F1D1D',
    'headerIcon' => '!',
])
    <p style="margin:0 0 16px;">نشكرك على اهتمامك بالانضمام إلى منصة صوت كصانع محتوى.</p>
    <p style="margin:0 0 16px;">نأسف لإبلاغك بأنه تعذّر قبول طلبك في الوقت الحالي.</p>

    @if ($reason)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
            @include('emails.partials.info-row', ['label' => 'السبب', 'value' => $reason, 'danger' => true])
        </table>
    @endif

    <p style="margin:0;color:#6b7280;font-size:13px;">يمكنك تقديم طلب جديد لاحقاً. نتمنى لك التوفيق.</p>
@endcomponent
