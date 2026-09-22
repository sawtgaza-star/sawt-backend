{{-- Courses → subscribe → accepted --}}
@php
    /** @var string $name */
    /** @var string $courseTitle */
    /** @var string|null $location */
    /** @var string|null $startsAt */
    /** @var string $courseUrl */
    /** @var string $incubatorUrl */
@endphp

@component('emails.layout', [
    'title' => 'تم قبول اشتراكك',
    'heading' => 'مرحباً '.$name,
    'subheading' => 'تم قبول طلب اشتراكك في الدورة بنجاح',
    'headerColor' => '#0F3D2E',
    'headerIcon' => '✓',
])
    <p style="margin:0 0 16px;">يسعدنا إخبارك بأنه تمت الموافقة على طلب اشتراكك في حاضنة صوت.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        @include('emails.partials.info-row', ['label' => 'اسم الدورة', 'value' => $courseTitle])
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

    @include('emails.partials.button', ['url' => $courseUrl, 'label' => 'عرض الدورة'])
    @include('emails.partials.link', ['url' => $incubatorUrl, 'label' => 'تصفح كورسات ثانية'])
@endcomponent
