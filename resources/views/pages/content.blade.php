@php
  $featuredSlides = [
    '/assets/images/1.png',
    '/assets/images/2.png',
    '/assets/images/3.png',
    '/assets/images/4.png',
    '/assets/images/1.png',
    '/assets/images/2.png',
    '/assets/images/3.png',
    '/assets/images/4.png',
  ];

  $categories = [
    ['id' => 'all', 'label' => 'الكل', 'i18n' => 'tab_all', 'active' => true],
    ['id' => 'palestine', 'label' => 'فلسطين', 'i18n' => 'tab_palestine'],
    ['id' => 'aqsa', 'label' => 'القدس والمسجد الأقصى', 'i18n' => 'tab_aqsa'],
    ['id' => 'economy', 'label' => 'الاقتصاد', 'i18n' => 'tab_economy'],
    ['id' => 'society', 'label' => 'المجتمع', 'i18n' => 'tab_society'],
  ];

  $contentCards = [
    ['img' => '/assets/images/1.png', 'duration' => '0:15', 'title' => 'قصة', 'subtitle' => 'من قلب الميدان', 'tag' => 'orange'],
    ['img' => '/assets/images/2.png', 'duration' => '1:00', 'title' => 'المصورون', 'subtitle' => 'قضية مهمشة!!', 'tag' => 'orange'],
    ['img' => '/assets/images/3.png', 'duration' => '0:42', 'title' => 'صوت الأرض', 'subtitle' => 'حكاية مستمرة', 'tag' => 'green'],
    ['img' => '/assets/images/4.png', 'duration' => '2:10', 'title' => 'نحن هنا', 'subtitle' => 'لن يُسكت صوتنا', 'tag' => 'orange'],
    ['img' => '/assets/images/1.png', 'duration' => '0:55', 'title' => 'ذاكرة', 'subtitle' => 'وجوه لا تُنسى', 'tag' => 'green'],
    ['img' => '/assets/images/2.png', 'duration' => '1:22', 'title' => 'رواية', 'subtitle' => 'من الميدان مباشرة', 'tag' => 'orange'],
    ['img' => '/assets/images/3.png', 'duration' => '0:38', 'title' => 'شهادة', 'subtitle' => 'صوت الحقيقة', 'tag' => 'orange'],
    ['img' => '/assets/images/4.png', 'duration' => '1:45', 'title' => 'أثر', 'subtitle' => 'يبدأ من صوت واحد', 'tag' => 'green'],
    ['img' => '/assets/images/1.png', 'duration' => '0:20', 'title' => 'رحلة', 'subtitle' => 'نحو الأمل', 'tag' => 'orange'],
    ['img' => '/assets/images/2.png', 'duration' => '2:05', 'title' => 'أهلنا', 'subtitle' => 'قصة تستحق أن تُروى', 'tag' => 'green'],
    ['img' => '/assets/images/3.png', 'duration' => '0:48', 'title' => 'واقع', 'subtitle' => 'كما هو', 'tag' => 'orange'],
    ['img' => '/assets/images/4.png', 'duration' => '1:12', 'title' => 'صوتكم', 'subtitle' => 'في كل مكان', 'tag' => 'orange'],
  ];

  $mostWatched = array_slice($contentCards, 0, 6);
  $mostWatchedAlt = array_slice($contentCards, 6, 6);
@endphp

@extends('layouts.app', [
  'activeNav' => 'content',
  'headerWrapperClass' => 'content-header',
])

@section('title', 'محتوانا — ' . ($siteName ?? 'Sawt'))

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <link rel="stylesheet" href="/assets/css/content.css" />
@endpush

@section('header_extra')
  <div class="container content-hero text-center text-white">
    <nav class="about-breadcrumb content-breadcrumb" aria-label="breadcrumb">
      <a href="{{ url('/') }}" data-i18n="nav_home">الرئيسية</a>
      <i class="fa-solid fa-angle-left mx-2 about-breadcrumb-sep arrow"></i>
      <span class="about-breadcrumb-active" data-i18n="nav_content">محتوانا</span>
    </nav>

    <h1 class="content-hero-title" data-i18n="welcome_title">
      كل فكرة إلها صوت… وصوت بيجمعهم
    </h1>
    <p class="content-hero-desc" data-i18n="content_hero_desc">
      مساحة تجمع القصص، الأصوات، والتجارب الحقيقية لنصنع معاً محتوى يصل ويعبّر
    </p>
  </div>

  <div class="content-featured-wrap">
    <div class="swiper contentFeaturedSwiper">
      <div class="swiper-wrapper" dir="rtl">
        @foreach ($featuredSlides as $i => $slide)
          <div class="swiper-slide">
            <img src="{{ $slide }}" alt="محتوى مميز {{ $i + 1 }}" />
          </div>
        @endforeach
      </div>
      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>
    </div>
  </div>
@endsection

@section('content')
  <main class="content-page-main">
    <section class="content-library section-pad">
      <div class="container">
        <div class="content-toolbar d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
          <div class="content-categories overflow-auto pb-1">
            <ul class="nav nav-pills content-filter-pills flex-nowrap" id="content-tabs" role="tablist">
              @foreach ($categories as $cat)
                <li class="nav-item me-2" role="presentation">
                  <button
                    class="nav-link rounded-pill px-4 {{ !empty($cat['active']) ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="pill"
                    data-bs-target="#tab-{{ $cat['id'] }}"
                    data-i18n="{{ $cat['i18n'] }}"
                  >
                    {{ $cat['label'] }}
                  </button>
                </li>
              @endforeach
            </ul>
          </div>

          <div class="content-sort d-flex align-items-center gap-2">
            <span class="content-sort-label fw-bold text-nowrap" data-i18n="sort_label">ترتيب حسب</span>
            <select class="form-select content-sort-select font-13" aria-label="ترتيب المحتوى">
              <option selected data-i18n="sort_most_viewed">الأكثر مشاهدة</option>
              <option value="latest" data-i18n="sort_latest">الأحدث</option>
              <option value="oldest" data-i18n="sort_oldest">الأقدم</option>
            </select>
            <button type="button" class="content-filter-icon" aria-label="تصفية">
              <i class="fa-solid fa-sliders"></i>
            </button>
          </div>
        </div>

        <div class="tab-content">
          @foreach ($categories as $cat)
            <div
              class="tab-pane fade {{ !empty($cat['active']) ? 'show active' : '' }}"
              id="tab-{{ $cat['id'] }}"
              role="tabpanel"
            >
              <div class="row g-3 g-lg-4 content-grid">
                @foreach ($contentCards as $card)
                  <div class="col-6 col-md-4 col-lg-2">
                    @include('pages.partials.content-card', ['card' => $card])
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <section class="most-watched-section section-pad">
      <div class="container">
        <div class="content-section-head d-flex justify-content-between align-items-center mb-4">
          <h2 class="content-section-title mb-0">
            <span data-i18n="most_watched">الأكثر مشاهدة</span>
          </h2>
          <a href="#" class="content-see-more" data-i18n="see_more">رؤية المزيد</a>
        </div>

        <div class="row g-3 g-lg-4 content-grid">
          @foreach ($mostWatched as $card)
            <div class="col-6 col-md-4 col-lg-2">
              @include('pages.partials.content-card', ['card' => $card])
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <section class="most-watched-section section-pad pt-0">
      <div class="container">
        <div class="content-section-head d-flex justify-content-between align-items-center mb-4">
          <h2 class="content-section-title mb-0">
            <span data-i18n="most_watched">الأكثر مشاهدة</span>
          </h2>
          <a href="#" class="content-see-more" data-i18n="see_more">رؤية المزيد</a>
        </div>

        <div class="row g-3 g-lg-4 content-grid">
          @foreach ($mostWatchedAlt as $card)
            <div class="col-6 col-md-4 col-lg-2">
              @include('pages.partials.content-card', ['card' => $card])
            </div>
          @endforeach
        </div>
      </div>
    </section>
  </main>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
    new Swiper('.contentFeaturedSwiper', {
      effect: 'coverflow',
      grabCursor: true,
      centeredSlides: true,
      loop: true,
      slidesPerView: 6,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      coverflowEffect: {
        depth: 200,
        rotate: 10,
        stretch: 0,
        modifier: 1,
        slideShadows: true,
      },
      breakpoints: {
        320: {
          slidesPerView: 3,
          coverflowEffect: { depth: 100, rotate: 15 },
        },
        992: {
          slidesPerView: 6,
        },
      },
      navigation: {
        nextEl: '.contentFeaturedSwiper .swiper-button-next',
        prevEl: '.contentFeaturedSwiper .swiper-button-prev',
      },
    });
  </script>
@endpush
