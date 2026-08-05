@php
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

$mediaUrl = function (?string $path, string $fallback): string {
    if (! filled($path)) {
        return $fallback;
    }
    if (str_starts_with($path, '/') || str_starts_with($path, 'http')) {
        return $path;
    }

    return Storage::disk('public')->url($path);
};

$logoUrl = $mediaUrl(Setting::get('home_logo'), '/assets/images/صوت 1.png');
$headerBg = $mediaUrl(Setting::get('about_header_bg'), '/assets/images/WhoUs.jpg');
$introImage = $mediaUrl(Setting::get('about_intro_image'), '/assets/images/tree.jpg');
$platformImage = $mediaUrl(Setting::get('about_platform_image'), '/assets/images/backgrounf_sawt.jpg');
$joinBg = $mediaUrl(Setting::get('about_join_bg'), '/assets/images/Yamal.png');

$i18nOverrides = ['ar' => [], 'en' => []];
$textKeys = [
    'about_hero_title' => 'about_hero_title',
    'about_hero_desc' => 'about_hero_desc',
    'about_header' => 'about_header',
    'about_intro' => 'about_intro',
    'about_platform_desc' => 'about_platform_desc',
    'about_core_values_subtitle' => 'core_values_subtitle',
    'about_story_subtitle' => 'sawt_story_subtitle',
    'about_join_title' => 'join_us_title',
    'about_join_desc' => 'join_us_desc',
    'about_join_button_text' => 'join_us_support',
];
foreach ($textKeys as $settingBase => $i18nKey) {
    $ar = Setting::get("{$settingBase}_ar");
    $en = Setting::get("{$settingBase}_en");
    if (filled($ar)) {
        $i18nOverrides['ar'][$i18nKey] = $ar;
    }
    if (filled($en)) {
        $i18nOverrides['en'][$i18nKey] = $en;
    }
}

$qAr = Setting::get('about_platform_question_ar');
$qEn = Setting::get('about_platform_question_en');
if (filled($qAr)) {
    $i18nOverrides['ar']['about_platform_question'] = $qAr;
    $i18nOverrides['ar']['about_platform_question_html'] = e($qAr);
}
if (filled($qEn)) {
    $i18nOverrides['en']['about_platform_question'] = $qEn;
    $i18nOverrides['en']['about_platform_question_html'] = e($qEn);
}

$coreValuesDefault = [
    ['title_ar' => 'المصداقية', 'title_en' => 'Credibility', 'desc_ar' => 'ننقل القصص والحقائق بدقة وموضوعية.', 'desc_en' => 'We convey stories and facts accurately.'],
    ['title_ar' => 'الإنسانية', 'title_en' => 'Humanity', 'desc_ar' => 'نضع الإنسان في قلب كل قصة.', 'desc_en' => 'We put the human at the heart of every story.'],
    ['title_ar' => 'التأثير', 'title_en' => 'Impact', 'desc_ar' => 'نسعى لصناعة محتوى يرفع الوعي.', 'desc_en' => 'We strive to create content that raises awareness.'],
    ['title_ar' => 'الاستقلالية', 'title_en' => 'Independence', 'desc_ar' => 'نلتزم بإعلام مستقل يعكس الواقع بصدق.', 'desc_en' => 'We are committed to independent media.'],
];
$coreValues = Setting::get('about_core_values', $coreValuesDefault);
if (! is_array($coreValues) || empty($coreValues)) {
    $coreValues = $coreValuesDefault;
}
foreach (array_values($coreValues) as $i => $v) {
    $n = $i + 1;
    if (filled($v['title_ar'] ?? null)) {
        $i18nOverrides['ar']["core_value_{$n}_title"] = $v['title_ar'];
    }
    if (filled($v['title_en'] ?? null)) {
        $i18nOverrides['en']["core_value_{$n}_title"] = $v['title_en'];
    }
    if (filled($v['desc_ar'] ?? null)) {
        $i18nOverrides['ar']["core_value_{$n}_desc"] = $v['desc_ar'];
    }
    if (filled($v['desc_en'] ?? null)) {
        $i18nOverrides['en']["core_value_{$n}_desc"] = $v['desc_en'];
    }
}

$storyDefault = [
    ['title_ar' => 'رحلتنا', 'title_en' => 'Our Journey', 'desc_ar' => 'بدأت رحلة «صوت» في ظل ظروف صعبة.', 'desc_en' => 'The journey of Sawt began under difficult circumstances.'],
    ['title_ar' => 'ما نقدم', 'title_en' => 'What We Offer', 'desc_ar' => 'نحن نقدم إعلاماً حقيقياً.', 'desc_en' => 'We provide genuine media.'],
    ['title_ar' => 'التأثير', 'title_en' => 'Impact', 'desc_ar' => 'منذ انطلاقنا، استطعنا إيصال أصوات آلاف.', 'desc_en' => 'Since launch, we have amplified thousands of voices.'],
];
$storyCards = Setting::get('about_story_cards', $storyDefault);
if (! is_array($storyCards) || empty($storyCards)) {
    $storyCards = $storyDefault;
}
foreach (array_values($storyCards) as $i => $c) {
    $n = $i + 1;
    if (filled($c['title_ar'] ?? null)) {
        $i18nOverrides['ar']["sawt_story_{$n}_title"] = $c['title_ar'];
    }
    if (filled($c['title_en'] ?? null)) {
        $i18nOverrides['en']["sawt_story_{$n}_title"] = $c['title_en'];
    }
    if (filled($c['desc_ar'] ?? null)) {
        $i18nOverrides['ar']["sawt_story_{$n}_desc"] = $c['desc_ar'];
    }
    if (filled($c['desc_en'] ?? null)) {
        $i18nOverrides['en']["sawt_story_{$n}_desc"] = $c['desc_en'];
    }
}

$heroTitle = Setting::get('about_hero_title_ar', 'صناع الأثر.. الفريق خلف منصة صوت');
$heroDesc = Setting::get('about_hero_desc_ar', '');
$headerTitle = Setting::get('about_header_ar', 'من نحن');
$introText = Setting::get('about_intro_ar', '');
$platformQuestion = Setting::get('about_platform_question_ar', 'ما الذي يدفعنا لنكون صوتك؟');
$platformDesc = Setting::get('about_platform_desc_ar', '');
$coreValuesSubtitle = Setting::get('about_core_values_subtitle_ar', '');
$storySubtitle = Setting::get('about_story_subtitle_ar', '');
$joinTitle = Setting::get('about_join_title_ar', 'لأن بعض الأصوات لا يجب أن تُنسى');
$joinDesc = Setting::get('about_join_desc_ar', '');
$joinButtonText = Setting::get('about_join_button_text_ar', 'مساهمة بإيصال صوت');
$joinButtonUrl = Setting::get('about_join_button_url') ?: route('donate');

$valueIcons = [
    '<svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 14 14"><path d="M0 0h14v14H0z" fill="none"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M9.568 1.255a.466.466 0 0 1 .864 0l.587 1.433l1.593.14c.416.036.578.56.255.824l-.947.778a.47.47 0 0 0-.16.46l.314 1.452a.466.466 0 0 1-.715.486L10 5.92l-1.359.91a.466.466 0 0 1-.715-.487L8.24 4.89a.47.47 0 0 0-.16-.459l-.947-.778a.466.466 0 0 1 .255-.825l1.593-.14zM.983 6.37l.692-.043a8 8 0 0 1 2.448.227l1.16.292a1.32 1.32 0 0 1 .99 1.416v0c-.078.765-.79 1.3-1.546 1.166L3.622 9.23l3.897.699l4.037-.958a1.24 1.24 0 0 1 1.482.887v0c.16.603-.153 1.23-.73 1.465l-3.23 1.311a6.93 6.93 0 0 1-4.918.113L.813 11.562"/></svg>',
    '<svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><path d="M18.709 3.495C16.817 2.554 14.5 2 12 2s-4.816.554-6.709 1.495c-.928.462-1.392.693-1.841 1.419S3 6.342 3 7.748v3.49c0 5.683 4.542 8.842 7.173 10.196c.734.377 1.1.566 1.827.566s1.093-.189 1.827-.566C16.457 20.08 21 16.92 21 11.237V7.748c0-1.406 0-2.108-.45-2.834s-.913-.957-1.841-1.419"/><path d="M9 11.5s1.408.252 2 2c0 0 1.5-3 4-4"/></g></svg>',
    '<svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 8a3 3 0 1 1-6 0a3 3 0 0 1 6 0m1-4a3 3 0 0 1 1.218 5.742M13.714 14h-3.428A4.286 4.286 0 0 0 6 18.286C6 19.233 6.767 20 7.714 20h8.572c.947 0 1.714-.767 1.714-1.714A4.286 4.286 0 0 0 13.714 14m4-1A4.286 4.286 0 0 1 22 17.286c0 .947-.767 1.714-1.714 1.714M8 4a3 3 0 0 0-1.218 5.742M3.714 19A1.714 1.714 0 0 1 2 17.286A4.286 4.286 0 0 1 6.286 13"/></svg>',
    '<svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5"><path d="M6.09 15a6.9 6.9 0 0 1-.59-2.795C5.5 8.502 8.41 5.5 12 5.5s6.5 3.003 6.5 6.706A6.9 6.9 0 0 1 17.91 15"/><path stroke-linejoin="round" d="M12 2v1m10 9h-1M3 12H2m17.07-7.072l-.707.707m-12.726.001l-.707-.707m9.587 14.377c1.01-.327 1.416-1.252 1.53-2.182c.034-.278-.195-.509-.475-.509H8.477a.483.483 0 0 0-.488.534c.112.928.394 1.607 1.464 2.157m5.064 0H9.453m5.064 0c-.121 1.945-.683 2.715-2.51 2.693c-1.954.036-2.404-.916-2.554-2.693"/></g></svg>',
];

$storyIcons = [
    '<svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" viewBox="0 0 14 14"><path d="M0 0h14v14H0z" fill="none"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M9.568 1.255a.466.466 0 0 1 .864 0l.587 1.433l1.593.14c.416.036.578.56.255.824l-.947.778a.47.47 0 0 0-.16.46l.314 1.452a.466.466 0 0 1-.715.486L10 5.92l-1.359.91a.466.466 0 0 1-.715-.487L8.24 4.89a.47.47 0 0 0-.16-.459l-.947-.778a.466.466 0 0 1 .255-.825l1.593-.14zM.983 6.37l.692-.043a8 8 0 0 1 2.448.227l1.16.292a1.32 1.32 0 0 1 .99 1.416v0c-.078.765-.79 1.3-1.546 1.166L3.622 9.23l3.897.699l4.037-.958a1.24 1.24 0 0 1 1.482.887v0c.16.603-.153 1.23-.73 1.465l-3.23 1.311a6.93 6.93 0 0 1-4.918.113L.813 11.562"/></svg>',
    '<svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.333 3c2.46-.003 4.836.887 6.667 2.5V21a10.07 10.07 0 0 0-6.667-2.5c-1.562 0-2.343 0-2.688-.22a1.16 1.16 0 0 1-.424-.425C2 17.51 2 16.895 2 15.663v-9.26c0-1.428 0-2.141.549-2.72c.548-.579 1.11-.609 2.234-.668Q5.056 3 5.333 3m13.334 0A10.07 10.07 0 0 0 12 5.5V21a10.07 10.07 0 0 1 6.667-2.5c1.562 0 2.343 0 2.688-.22c.207-.133.291-.218.424-.425c.221-.345.221-.96.221-2.192v-9.26c0-1.428 0-2.141-.549-2.72s-1.11-.609-2.234-.668Q18.944 3 18.667 3"/></svg>',
    '<svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><g fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18.719 10.715a1.044 1.044 0 0 1-1.437 0c-1.765-1.683-4.13-3.564-2.977-6.294C14.929 2.945 16.425 2 18 2s3.072.945 3.695 2.42c1.152 2.728-1.207 4.617-2.977 6.295Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M18.135 6h-.125m.25 0a.25.25 0 1 1-.5 0a.25.25 0 0 1 .5 0"/><circle cx="5" cy="19" r="3" stroke-linecap="round" stroke-linejoin="round"/><path stroke-linecap="round" stroke-linejoin="round" d="M11 7H9.5C7.567 7 6 8.343 6 10s1.567 3 3.5 3h3c1.933 0 3.5 1.343 3.5 3s-1.567 3-3.5 3H11m7.135-13h-.125m.25 0a.25.25 0 1 1-.5 0a.25.25 0 0 1 .5 0"/></g></svg>',
];
@endphp

@extends('layouts.app', [
    'activeNav' => 'about',
    'headerWrapperClass' => 'about-header',
    'headerWrapperStyle' => "background: url('{$headerBg}')",
    'i18nOverrides' => $i18nOverrides,
])

@section('title', $headerTitle . ' — ' . Setting::get('site_name', 'Sawt'))

@section('header_extra')
  <div class="container about-hero text-center text-white">
    <nav class="about-breadcrumb" aria-label="breadcrumb">
      <a href="{{ url('/') }}" data-i18n="nav_home">الرئيسية</a>
      <i class="fa-solid fa-angle-left mx-2 about-breadcrumb-sep arrow"></i>
      <span class="about-breadcrumb-active" data-i18n="nav_about">من نحن</span>
    </nav>
    <h1 class="about-hero-title" data-i18n="about_hero_title">{{ $heroTitle }}</h1>
    <p class="about-hero-desc" data-i18n="about_hero_desc">{{ $heroDesc }}</p>
  </div>
@endsection

@section('content')
  <section>
    <div class="about-sec container" style="margin-top: 50px; z-index: 1">
      <div class="row align-items-center">
        <div class="col-12 col-lg-6 about-sec-content" dir="rtl">
          <h2 class="about-sec-title" data-i18n="about_header">{{ $headerTitle }}</h2>
          <p class="about-sec-desc" data-i18n="about_intro">{{ $introText }}</p>
        </div>
        <div class="col-12 col-lg-6 mt-4 about-sec-img-col">
          <div class="about-sec-img-wrapper">
            <img src="{{ $introImage }}" alt="" class="about-sec-img" />
            <div class="member-card about-sec-leaf" dir="rtl">
              <img src="{{ $logoUrl }}" alt="" width="120" height="70" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="core-values-section">
    <img src="/assets/images/leaf_cutout.png" class="olive-branch branch-right-bottom-about" alt="Olive Branch" />
    <img src="/assets/images/leaf_cutout.png" class="olive-branch branch-left-bottom-about" alt="Olive Branch" />

    <div class="container" dir="rtl">
      <div class="text-center core-values-head">
        <h2 class="core-values-title" data-i18n-html="core_values_title">
          أهم القيم التي
          <span class="core-values-highlight">نركز عليها</span>
        </h2>
        <p class="core-values-subtitle" data-i18n="core_values_subtitle">{{ $coreValuesSubtitle }}</p>
      </div>

      <div class="row g-4 justify-content-center core-values-grid">
        @foreach (array_values($coreValues) as $i => $value)
          @php $n = $i + 1; @endphp
          <div class="col-12 col-sm-6 col-lg-3">
            <div class="value-card">
              <div class="value-card-icon">
                @if (! empty($value['icon']))
                  <img src="{{ $mediaUrl($value['icon'], '') }}" alt="" width="28" height="28" />
                @else
                  <i>{!! $valueIcons[$i % count($valueIcons)] !!}</i>
                @endif
              </div>
              <h3 class="value-card-title" data-i18n="core_value_{{ $n }}_title">
                {{ $value['title_ar'] ?? '' }}
              </h3>
              <p class="value-card-desc" data-i18n="core_value_{{ $n }}_desc">
                {{ $value['desc_ar'] ?? '' }}
              </p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <section class="about-the-platform">
    <div class="about-the-platform-card">
      <div style="border-radius: 20px !important; background-color: rgba(237, 239, 235, 1) !important">
        <img class="birds-img" src="/assets/images/birds.png" alt="" />
        <div class="row align-items-center g-0" dir="ltr">
          <div class="col-12 col-lg-5 about-the-platform-content">
            <h2 class="about-the-platform-question" data-i18n="about_platform_question">
              {{ $platformQuestion }}
            </h2>
            <p class="about-the-platform-desc" data-i18n="about_platform_desc">
              {{ $platformDesc }}
            </p>
          </div>
          <div class="col-12 col-lg-7 about-the-platform-visual">
            <img src="{{ $platformImage }}" alt="صوت" class="about-platform-img" />
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="sawt-story-section">
    <img src="/assets/images/leaf_cutout.png" class="olive-branch branch-right-bottom-sawt-story" alt="Olive Branch" />
    <div class="container" dir="rtl">
      <div class="text-center sawt-story-head">
        <h2 class="sawt-story-title" data-i18n-html="sawt_story_title_html">
          <span>قصة </span>
          <span class="sawt-story-highlight">صوت</span>
        </h2>
        <p class="sawt-story-subtitle" data-i18n="sawt_story_subtitle">{{ $storySubtitle }}</p>
      </div>

      <div class="row g-4 justify-content-center sawt-story-grid">
        @foreach (array_values($storyCards) as $i => $card)
          @php $n = $i + 1; @endphp
          <div class="col-12 col-md-6 col-lg-4 sawt-story-grid-col">
            <div class="sawt-story-card">
              <div class="sawt-story-icon">
                @if (! empty($card['icon']))
                  <img src="{{ $mediaUrl($card['icon'], '') }}" alt="" width="24" height="24" />
                @else
                  <i>{!! $storyIcons[$i % count($storyIcons)] !!}</i>
                @endif
              </div>
              <h3 class="sawt-story-card-title" data-i18n="sawt_story_{{ $n }}_title">
                {{ $card['title_ar'] ?? '' }}
              </h3>
              <p class="sawt-story-card-desc" data-i18n="sawt_story_{{ $n }}_desc">
                {{ $card['desc_ar'] ?? '' }}
              </p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <section class="join-us-section">
    <div class="join-us-banner-about">
      <img src="{{ $joinBg }}" alt="" class="join-us-bg-about" />
      <div class="join-us-content text-center">
        <h2 class="join-us-title" data-i18n="join_us_title">{{ $joinTitle }}</h2>
        <p class="join-us-desc" data-i18n="join_us_desc">{{ $joinDesc }}</p>
        <a href="{{ $joinButtonUrl }}" class="btn btn-dark-green join-us-btn">
          <span data-i18n="join_us_support">{{ $joinButtonText }}</span>
          <i class="fa-solid fa-angle-left arrow"></i>
        </a>
      </div>
    </div>
  </section>
@endsection
