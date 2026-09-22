{{-- Courses → join → accepted --}}
@php
    /** @var string $name */
    /** @var string $courseTitle */
    /** @var string|null $location */
    /** @var string|null $startsAt */
    /** @var string $courseUrl */
    /** @var string $incubatorUrl */
@endphp

@component('emails.layout', [
    'title' => 'تم قبول طلب الانضمام',
    'heading' => 'مرحباً '.$name,
    'subheading' => 'تم قبول طلب انضمامك إلى الكورس بنجاح',
    'headerColor' => '#0F3D2E',
    'headerIcon' => '✓',
])
    <p style="margin:0 0 16px;">يسعدنا إخبارك بأنه تمت الموافقة على طلبك في حاضنة صوت.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        @include('emails.partials.info-row', ['label' => 'اسم الكورس', 'value' => $courseTitle])
        @if ($location)
            <tr><td style="height:10px;"></td></tr>
            @include('emails.partials.info-row', ['label' => 'المكان', 'value' => $location])
        @endif
        @if ($startsAt)
            <tr><td style="height:10px;"></td></tr>
            @include('emails.partials.info-row', ['label' => 'تاريخ البدء', 'value' => $startsAt])
        @endif
    </table>

    <p style="margin:0 0 22px;">سيتواصل معك فريق صوت بتفاصيل الحضور والمواعيد.</p>

    @include('emails.partials.button', ['url' => $courseUrl, 'label' => 'عرض الكورس'])
    @include('emails.partials.link', ['url' => $incubatorUrl, 'label' => 'تصفح كورسات ثانية'])

    <p style="margin:24px 0 0;color:#6b7280;font-size:13px;">شكراً لانضمامك إلى منصة صوت.</p>
@endcomponent
