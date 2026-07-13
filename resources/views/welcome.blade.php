@php
    use App\Models\Setting;
    use Illuminate\Support\Facades\Storage;

    $logoRaw = Setting::get('home_logo');
    $logoUrl = $logoRaw ? Storage::disk('public')->url($logoRaw) : '/assets/images/صوت 1.png';

    $heroRaw = Setting::get('home_hero_image');
    $heroUrl = $heroRaw ? Storage::disk('public')->url($heroRaw) : '/assets/images/swat.png';

    // النصوص القابلة للتعديل ثنائية اللغة — تُحقن فوق قاموس translate.js
    $i18nKeys = ['who_we_are', 'welcome_lead', 'welcome_title', 'welcome_desc'];
    $i18nOverrides = ['ar' => [], 'en' => []];
    foreach ($i18nKeys as $k) {
        $ar = Setting::get("home_{$k}_ar");
        $en = Setting::get("home_{$k}_en");
        if (filled($ar)) $i18nOverrides['ar'][$k] = $ar;
        if (filled($en)) $i18nOverrides['en'][$k] = $en;
    }

    // شرائح الكاروسيل الرئيسي — قابلة للإضافة/الحذف من لوحة التحكم
    $heroDefault = [
        ['image' => '', 'title_ar' => 'منصة صوت', 'title_en' => 'Sawt Platform', 'subtitle_ar' => 'نروي قصص غزة بكرامة... ونبني جيلاً جديداً من صناع المحتوى', 'subtitle_en' => "We tell Gaza's stories with dignity and build a new generation of creators"],
        ['image' => '', 'title_ar' => 'منصة صوت', 'title_en' => 'Sawt Platform', 'subtitle_ar' => 'نروي قصص غزة بكرامة... ونبني جيلاً جديداً من صناع المحتوى', 'subtitle_en' => "We tell Gaza's stories with dignity and build a new generation of creators"],
        ['image' => '', 'title_ar' => 'منصة صوت', 'title_en' => 'Sawt Platform', 'subtitle_ar' => 'نروي قصص غزة بكرامة... ونبني جيلاً جديداً من صناع المحتوى', 'subtitle_en' => "We tell Gaza's stories with dignity and build a new generation of creators"],
    ];
    $heroDefaultImgs = ['/assets/images/heroSectionImg.jpeg', '/assets/images/backgrounf_sawt.jpg', '/assets/images/tree.jpg'];

    $heroSlidesRaw = Setting::get('home_hero_slides', $heroDefault);
    if (! is_array($heroSlidesRaw) || empty($heroSlidesRaw)) $heroSlidesRaw = $heroDefault;

    $heroSlides = [];
    foreach (array_values($heroSlidesRaw) as $hi => $hs) {
        $img = $hs['image'] ?? '';
        if (! $img) {
            $img = $heroDefaultImgs[$hi] ?? $heroDefaultImgs[0];
        } elseif (! str_starts_with($img, '/') && ! str_starts_with($img, 'http')) {
            $img = Storage::disk('public')->url($img);
        }
        $heroSlides[] = ['image' => $img];

        if (filled($hs['title_ar'] ?? null)) $i18nOverrides['ar']["hero_title_{$hi}"] = $hs['title_ar'];
        if (filled($hs['title_en'] ?? null)) $i18nOverrides['en']["hero_title_{$hi}"] = $hs['title_en'];
        if (filled($hs['subtitle_ar'] ?? null)) $i18nOverrides['ar']["hero_subtitle_{$hi}"] = $hs['subtitle_ar'];
        if (filled($hs['subtitle_en'] ?? null)) $i18nOverrides['en']["hero_subtitle_{$hi}"] = $hs['subtitle_en'];

        // النص الظاهر افتراضياً قبل تشغيل الترجمة (عربي)
        $heroSlides[$hi]['title'] = $hs['title_ar'] ?? '';
        $heroSlides[$hi]['subtitle'] = $hs['subtitle_ar'] ?? '';
    }
@endphp

@extends('layouts.app', [
    'activeNav' => 'home',
    'headerWrapperClass' => 'main-header-wrapper',
    'i18nOverrides' => $i18nOverrides,
])

@section('title', Setting::get('site_name', 'Sawt'))

@push('styles')
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"
    integrity="sha512-1cK78a1o+ht2JcaW6g8OXYwqpev9+6GqOkz9xmBN9iUUhIndKtxwILGWYOSibOKjLsEdjyjZvYDq/cZwNeak0w=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css"
    integrity="sha512-H9jrZiiopUdsLpg94A333EfumgUBpO9MdbxStdeITo+KEIMaNfHNvwyjjDJb+ERPaRS6DpyRlKbvPUasNItRyw=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
@endpush
@section('header_extra')
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-indicators">
            @foreach ($heroSlides as $hi => $hs)
            <div>
              <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $hi }}" @if($hi === 0) class="active" @endif></button>
            </div>
            @endforeach
          </div>

          <div class="carousel-inner">
            @foreach ($heroSlides as $hi => $hs)
            <div class="carousel-item position-relative @if($hi === 0) active @endif">
              <div class="overlay"></div>
              <img src="{{ $hs['image'] }}" class="d-block w-100 carousel-img" alt="منصة صوت" />
              <div class="carousel-caption-custom text-center">
                <div class="container">
                  <div class="d-md-flex justify-content-center gap-1 mb-4 align-items-center">
                    <i class="fa-solid fa-star yellow-stars"></i>
                    <i class="fa-solid fa-star yellow-stars"></i>
                    <i class="fa-solid fa-star yellow-stars"></i>
                    <i class="fa-solid fa-star yellow-stars"></i>
                    <i class="fa-regular fa-star gray-star"></i>
                    <p class="text-white hero-subtitle mb-0" data-i18n="hero_trust">ثقة آلاف المتابعين في منصة صوت غزة بصدق وتأثير</p>
                  </div>
                  <h1 class="fw-bold text-white font-60" data-i18n="hero_title_{{ $hi }}">{{ $hs['title'] }}</h1>
                  <p class="mb-4 text-white font-24" data-i18n="hero_subtitle_{{ $hi }}">{{ $hs['subtitle'] }}</p>
                  <div class="d-flex justify-content-center gap-3 heroOptionsBtn">
                    <button class="btn rounded-pill px-4 py-2 text-white fw-bold hero-btn-watch" style="background-color: rgba(76, 92, 55, 1)">
                      <span class="ms-2" data-i18n="hero_btn_watch">ادعم صوت</span>
                      <i class="fa-solid fa-angle-left"></i>
                    </button>
                    <button class="btn rounded-pill px-4 py-2 text-white fw-bold hero-btn-support" data-i18n="hero_btn_collab">تعاون معنا</button>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>

          <button
            class="carousel-control-prev hero-arrow"
            type="button"
            data-bs-target="#heroCarousel"
            data-bs-slide="prev"
          >
            <span class="hero-arrow-icon" aria-hidden="true">
              <i class="fa-solid fa-chevron-left"></i>
            </span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button
            class="carousel-control-next hero-arrow"
            type="button"
            data-bs-target="#heroCarousel"
            data-bs-slide="next"
          >
            <span class="hero-arrow-icon" aria-hidden="true">
              <i class="fa-solid fa-chevron-right"></i>
            </span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>

      <div class="stats-bar">
        <div class="box-element container front-face text-white rounded-4 py-4">
          <div
            class="row d-flex justify-content-center align-items-center text-center g-0"
          >
            <div class="col count">
              <i>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="3em"
                  height="3em"
                  viewBox="0 0 24 24"
                >
                  <path d="M0 0h24v24H0z" fill="none" />
                  <g
                    fill="none"
                    stroke="rgba(255, 116, 32, 1)"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                  >
                    <path
                      d="M16.5 20v-2.03c0-1.242-.56-2.46-1.69-2.975C13.431 14.366 11.778 14 10 14s-3.431.366-4.81.995c-1.13.515-1.69 1.733-1.69 2.975V20m17 .001v-2.03c0-1.242-.56-2.46-1.69-2.975q-.39-.18-.81-.328"
                    />
                    <circle cx="10" cy="7.5" r="3.5" />
                    <path d="M15 4.145a3.502 3.502 0 0 1 0 6.71" />
                  </g>
                </svg>
              </i>

              <h3 class="counter font-mob-22">{{ Setting::get('home_stat_team', '20+') }}</h3>
              <p class="mb-0" data-i18n="stat_team">أعضاء الفريق</p>
            </div>
            <div class="col count">
              <!-- <i class="fa-solid fa-book-open"></i> -->
              <i>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="3em"
                  height="3em"
                  viewBox="0 0 24 24"
                >
                  <path d="M0 0h24v24H0z" fill="none" />
                  <path
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M5.333 3c2.46-.003 4.836.887 6.667 2.5V21a10.07 10.07 0 0 0-6.667-2.5c-1.562 0-2.343 0-2.688-.22a1.16 1.16 0 0 1-.424-.425C2 17.51 2 16.895 2 15.663v-9.26c0-1.428 0-2.141.549-2.72c.548-.579 1.11-.609 2.234-.668Q5.056 3 5.333 3m13.334 0A10.07 10.07 0 0 0 12 5.5V21a10.07 10.07 0 0 1 6.667-2.5c1.562 0 2.343 0 2.688-.22c.207-.133.291-.218.424-.425c.221-.345.221-.96.221-2.192v-9.26c0-1.428 0-2.141-.549-2.72s-1.11-.609-2.234-.668Q18.944 3 18.667 3"
                  />
                </svg>
              </i>
              <h3 class="font-mob-22 counter">{{ Setting::get('home_stat_stories', '100+') }}</h3>
              <p class="mb-0" data-i18n="stat_stories">قصة</p>
            </div>
            <div class="col count">
              <i>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="3em"
                  height="3em"
                  viewBox="0 0 24 24"
                >
                  <path d="M0 0h24v24H0z" fill="none" />
                  <g fill="none" stroke="currentColor" stroke-width="1.5">
                    <path
                      d="M21.544 11.045c.304.426.456.64.456.955c0 .316-.152.529-.456.955C20.178 14.871 16.689 19 12 19c-4.69 0-8.178-4.13-9.544-6.045C2.152 12.529 2 12.315 2 12c0-.316.152-.529.456-.955C3.822 9.129 7.311 5 12 5c4.69 0 8.178 4.13 9.544 6.045Z"
                    />
                    <path d="M15 12a3 3 0 1 0-6 0a3 3 0 0 0 6 0Z" />
                  </g>
                </svg>
              </i>

              <h3 class="font-mob-22">
                {{ Setting::get('home_stat_views', '+30') }} <span data-i18n="one_thousand"></span>
              </h3>
              <p class="mb-0" data-i18n="stat_views">مشاهدة</p>
            </div>
            <div class="col count">
              <i>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="3em"
                  height="3em"
                  viewBox="0 0 24 24"
                >
                  <path d="M0 0h24v24H0z" fill="none" />
                  <g
                    fill="none"
                    stroke="currentColor"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                  >
                    <path
                      stroke-linecap="round"
                      d="M17.7 21.335c-1.172.165-2.7.165-4.75.165h-1.9c-4.03 0-6.046 0-7.298-1.252S2.5 16.98 2.5 12.95v-1.9c0-4.03 0-6.046 1.252-7.298S7.02 2.5 11.05 2.5h1.9c4.03 0 6.046 0 7.298 1.252S21.5 7.019 21.5 11.05v1.9c0 1.208 0 2.235-.034 3.115c-.027.705-.04 1.057-.307 1.19c-.267.13-.566-.08-1.163-.503L18.65 15.8"
                    />
                    <path
                      d="M14.945 12.395c-.176.627-1.012 1.07-2.682 1.955c-1.615.856-2.422 1.285-3.073 1.113a1.66 1.66 0 0 1-.712-.393C8 14.62 8 13.746 8 12s0-2.62.478-3.07c.198-.186.443-.321.712-.392c.65-.173 1.458.256 3.073 1.112c1.67.886 2.506 1.329 2.682 1.955c.073.259.073.531 0 .79Z"
                    />
                  </g>
                </svg>
              </i>

              <h3 class="counter font-mob-22">{{ Setting::get('home_stat_videos', '30+') }}</h3>
              <p class="mb-0" data-i18n="stat_videos">فيديو</p>
            </div>
            <div class="col count">
              <i>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="3em"
                  height="3em"
                  viewBox="0 0 24 24"
                >
                  <path d="M0 0h24v24H0z" fill="none" />
                  <g
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                  >
                    <path
                      d="M7.5 19.5c0-.966.329-1.942 1.13-2.48A6.04 6.04 0 0 1 12 16c1.248 0 2.407.376 3.37 1.02c.802.538 1.13 1.514 1.13 2.48"
                    />
                    <circle cx="12" cy="11" r="2.5" />
                    <path
                      d="M17.5 11c1.11 0 2.142.377 2.997 1.022c.726.548 1.003 1.473 1.003 2.382v.096"
                    />
                    <circle cx="17.5" cy="6.5" r="2" />
                    <path
                      d="M6.5 11c-1.11 0-2.142.377-2.997 1.022c-.726.548-1.003 1.473-1.003 2.382v.096"
                    />
                    <circle cx="6.5" cy="6.5" r="2" />
                  </g>
                </svg>
              </i>
              <h3 class="font-mob-22">
                {{ Setting::get('home_stat_followers', '+10') }} <span data-i18n="one_thousand"></span>
              </h3>

              <p class="mb-0" data-i18n="stat_followers">متابع</p>
            </div>
          </div>
        </div>
      </div>
@endsection

@section('content')
    <main class="my-5">
      <section class="sout-section py-5">
        <div class="container">
          <div class="text-center" style="margin-bottom: 70px">
            <h1 class="fw-bold who-us font-42" data-i18n="who_we_are">
              من نحن
            </h1>
            <p
              class="mt-2 font-24"
              style="color: rgba(72, 72, 72, 1)"
              data-i18n="welcome_lead"
            >
              في صوت، كل فكرة بتلاقي مكانها!
            </p>
          </div>
          <div class="row">
            <div
              class="col-lg-6 position-relative mt-5"
              style="text-align: start"
            >
              <h3
                class="main-title text-bold fw-bold"
                data-i18n="welcome_title"
              >
                كل فكرة إلها صوت... وصوت بيجمعهم
              </h3>

              <p
                class="description text-secondary font-18 lh-lg"
                style="color: rgba(90, 90, 90, 1) !important"
                data-i18n="welcome_desc"
              >
                استكشف محتوى متنوع، عبّر عن نفسك، وشارك صوتك مع العالم، من خلال
                تجربة تفاعلية مليئة بالإبداع والإلهام، رح تقدر تطوّر أفكارك
                وتوصل لجمهور أوسع، وصوت بيكون معك خطوة بخطوة لتخلي صوتك يوصل
                أبعد.
              </p>

              <div class="features-grid row gap-2 my-4">
                <div class="col-6 feature-item d-flex align-items-center">
                  <div class="icon-box">
                    <i>
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="1.2em"
                        height="1.2em"
                        viewBox="0 0 24 24"
                      >
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path
                          fill="none"
                          stroke="currentColor"
                          stroke-width="1.5"
                          d="M21 6.5a3 3 0 1 1-6 0a3 3 0 0 1 6 0ZM9 12a3 3 0 1 1-6 0a3 3 0 0 1 6 0Zm12 5.5a3 3 0 1 1-6 0a3 3 0 0 1 6 0ZM8.729 10.75l6.5-3m-6.5 5.5l6.5 3"
                        />
                      </svg>
                    </i>
                  </div>

                  <span
                    class="me-md-1 ms-2 text-bold font-18 fw-bold"
                    data-i18n="feature_publish"
                    >خدمات تُسهّل النشر.</span
                  >
                </div>
                <div class="col feature-item d-flex align-items-center">
                  <div class="icon-box">
                    <i>
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="1.2em"
                        height="1.2em"
                        viewBox="0 0 14 14"
                      >
                        <path d="M0 0h14v14H0z" fill="none" />
                        <path
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M9.568 1.255a.466.466 0 0 1 .864 0l.587 1.433l1.593.14c.416.036.578.56.255.824l-.947.778a.47.47 0 0 0-.16.46l.314 1.452a.466.466 0 0 1-.715.486L10 5.92l-1.359.91a.466.466 0 0 1-.715-.487L8.24 4.89a.47.47 0 0 0-.16-.459l-.947-.778a.466.466 0 0 1 .255-.825l1.593-.14zM.983 6.37l.692-.043a8 8 0 0 1 2.448.227l1.16.292a1.32 1.32 0 0 1 .99 1.416v0c-.078.765-.79 1.3-1.546 1.166L3.622 9.23l3.897.699l4.037-.958a1.24 1.24 0 0 1 1.482.887v0c.16.603-.153 1.23-.73 1.465l-3.23 1.311a6.93 6.93 0 0 1-4.918.113L.813 11.562"
                        />
                      </svg>
                    </i>
                  </div>

                  <span
                    class="me-md-1 ms-2 text-bold font-18 fw-bold"
                    data-i18n="feature_empower_creativity"
                    >مساحة لتمكين الإبداع</span
                  >
                </div>

                <div class="col-6 feature-item d-flex align-items-center">
                  <div class="icon-box">
                    <i>
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="1.2em"
                        height="1.2em"
                        viewBox="0 0 24 24"
                      >
                        <path d="M0 0h24v24H0z" fill="none" />
                        <g
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                        >
                          <path
                            d="M16.5 20v-2.03c0-1.242-.56-2.46-1.69-2.975C13.431 14.366 11.778 14 10 14s-3.431.366-4.81.995c-1.13.515-1.69 1.733-1.69 2.975V20m17 .001v-2.03c0-1.242-.56-2.46-1.69-2.975q-.39-.18-.81-.328"
                          />
                          <circle cx="10" cy="7.5" r="3.5" />
                          <path d="M15 4.145a3.502 3.502 0 0 1 0 6.71" />
                        </g>
                      </svg>
                    </i>
                  </div>

                  <span
                    class="me-md-1 ms-2 text-bold font-18 fw-bold"
                    data-i18n="feature_expert_team"
                    >فريق خبراء يدعمك
                  </span>
                </div>
                <div class="col feature-item d-flex align-items-center">
                  <div class="icon-box">
                    <i
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="1.2em"
                        height="1.2em"
                        viewBox="0 0 24 24"
                      >
                        <path d="M0 0h24v24H0z" fill="none" />
                        <g fill="none" stroke="currentColor" stroke-width="1.5">
                          <path d="M17 7v4a5 5 0 0 1-10 0V7a5 5 0 0 1 10 0Z" />
                          <path
                            stroke-linecap="round"
                            d="M17 7h-3m3 4h-3m6 0a8 8 0 0 1-8 8m0 0a8 8 0 0 1-8-8m8 8v3m0 0h3m-3 0H9"
                          />
                        </g></svg
                    ></i>
                  </div>

                  <span
                    class="me-md-1 ms-2 text-bold font-18 fw-bold"
                    data-i18n="feature_express_voice"
                  >
                    محتوى يعبّر عن صوتك
                  </span>
                </div>
              </div>

              <a
                href="#"
                class="btn btn-dark-green rounded-pill px-4 py-2 font-16 text-bold fw-bold"
                style="border-radius: 18px !important"
              >
                <span class="" data-i18n="discover_more">اكتشف المزيد</span>
                <i
                  class="fa-solid fa-angle-left me-2 font-14 text-bold arrow"
                ></i>
              </a>
            </div>

            <div class="col-lg-6 mt-2">
              <img class="image-swat" src="{{ $heroUrl }}" alt="" />
            </div>
          </div>
        </div>
      </section>

      <section class="latest-news py-5 position-relative">
        <div class="bg-icon bg-icon-right">
          <img src="/assets/images/fa-solid_microphone-alt.png" alt="" />
        </div>
        <div class="bg-icon bg-icon-left">
          <img src="/assets/images/fa-solid_microphone-alt (1).png" alt="" />
        </div>
        <div class="container">
          <div class="text-center mb-2">
            <h2 class="fw-bold who-us font-42">
              <span data-i18n="news_title_pre">آخر</span>
              <span data-i18n="news_title_highlight">أخبارنا</span>
            </h2>
            <p
              class="news-subtitle font-24"
              style="
                color: rgba(90, 90, 90, 1);
                margin: 20px 0px 35px 0px !important;
              "
              data-i18n="news_subtitle"
            >
              شاهد أحدث القصص والفيديوهات من منصة صوت
            </p>
          </div>
          <div class="owl-carousel creators-carousel">
            <div class="item">
              <div class="card h-100 news-card">
                <img
                  src="/assets/images/Rectangle 701.png"
                  class="card-img-top"
                  alt="صانع المحتوى"
                />
                <div class="card-body">
                  <h5 class="card-title fw-bold" data-i18n="news_card1_title">
                    صانع المحتوى في غزة
                  </h5>
                  <p
                    class="card-text font-md-18"
                    style="font-weight: 500; color: rgba(109, 109, 109, 1)"
                    data-i18n="news_desc"
                  >
                    نشارككم آخر تحديثات صانع المحتوى في غزة، حيث نعمل على إبراز
                    قصص المبدعين وإيصال صوتهم.
                  </p>
                </div>
                <div
                  class="card-footer bg-white border-0 d-flex font-16 text-dark pb-3 fw-bold"
                >
                  <span>
                    <i style="color: rgba(109, 109, 109, 1)">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="1.2em"
                        height="1.2em"
                        viewBox="0 0 24 24"
                      >
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          d="M16 2v4M8 2v4m5-2h-2C7.229 4 5.343 4 4.172 5.172S3 8.229 3 12v2c0 3.771 0 5.657 1.172 6.828S7.229 22 11 22h2c3.771 0 5.657 0 6.828-1.172S21 17.771 21 14v-2c0-3.771 0-5.657-1.172-6.828S16.771 4 13 4M3 10h18m-10 4h5m-8 0h.009M13 18H8m8 0h-.009"
                        />
                      </svg>
                    </i>

                    <span
                      data-i18n="news_date"
                      style="color: rgba(109, 109, 109, 1)"
                      >5 مارس 2026</span
                    >
                  </span>
                  <span class="readmore">
                    <a href="#">
                      <span
                        style="color: rgba(76, 92, 55, 1)"
                        data-i18n="read_more"
                        >اقرأ المزيد</span
                      >
                      <i
                        class="fa-solid fa-angle-left me-2 ms-1 arrow"
                        style="color: rgba(76, 92, 55, 1)"
                      ></i>
                    </a>
                  </span>
                </div>
              </div>
            </div>
            <div class="item">
              <div class="card h-100 news-card">
                <img
                  src="/assets/images/Rectangle 703.png"
                  class="card-img-top"
                  alt="الام في غزة "
                />
                <div class="card-body">
                  <h5 class="card-title fw-bold" data-i18n="news_card2_title">
                    الام في غزة
                  </h5>
                  <p
                    class="card-text font-md-18"
                    style="font-weight: 500; color: rgba(109, 109, 109, 1)"
                    data-i18n="news_desc"
                  >
                    نشارككم آخر تحديثات صانع المحتوى في غزة، حيث نعمل على إبراز
                    قصص المبدعين وإيصال صوتهم.
                  </p>
                </div>
                <div
                  class="card-footer bg-white border-0 d-flex font-16 text-dark pb-3 fw-bold"
                >
                  <span>
                    <i style="color: rgba(109, 109, 109, 1)">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="1.2em"
                        height="1.2em"
                        viewBox="0 0 24 24"
                      >
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          d="M16 2v4M8 2v4m5-2h-2C7.229 4 5.343 4 4.172 5.172S3 8.229 3 12v2c0 3.771 0 5.657 1.172 6.828S7.229 22 11 22h2c3.771 0 5.657 0 6.828-1.172S21 17.771 21 14v-2c0-3.771 0-5.657-1.172-6.828S16.771 4 13 4M3 10h18m-10 4h5m-8 0h.009M13 18H8m8 0h-.009"
                        />
                      </svg>
                    </i>

                    <span
                      data-i18n="news_date"
                      style="color: rgba(109, 109, 109, 1)"
                      >5 مارس 2026</span
                    >
                  </span>
                  <span class="readmore">
                    <a href="#">
                      <span
                        style="color: rgba(76, 92, 55, 1)"
                        data-i18n="read_more"
                        >اقرأ المزيد</span
                      >
                      <i
                        class="fa-solid fa-angle-left me-2 ms-1 arrow"
                        style="color: rgba(76, 92, 55, 1)"
                      ></i>
                    </a>
                  </span>
                </div>
              </div>
            </div>
            <div class="item">
              <div class="card h-100 news-card">
                <img
                  src="/assets/images/Rectangle 705.png"
                  class="card-img-top"
                  alt="صانع المحتوى"
                />
                <div class="card-body">
                  <h5 class="card-title fw-bold" data-i18n="news_card1_title">
                    صانع المحتوى في غزة
                  </h5>
                  <p
                    class="card-text font-md-18"
                    style="font-weight: 500; color: rgba(109, 109, 109, 1)"
                    data-i18n="news_desc"
                  >
                    نشارككم آخر تحديثات صانع المحتوى في غزة، حيث نعمل على إبراز
                    قصص المبدعين وإيصال صوتهم.
                  </p>
                </div>
                <div
                  class="card-footer bg-white border-0 d-flex font-16 text-dark pb-3 fw-bold"
                >
                  <span>
                    <i style="color: rgba(109, 109, 109, 1)">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="1.2em"
                        height="1.2em"
                        viewBox="0 0 24 24"
                      >
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          d="M16 2v4M8 2v4m5-2h-2C7.229 4 5.343 4 4.172 5.172S3 8.229 3 12v2c0 3.771 0 5.657 1.172 6.828S7.229 22 11 22h2c3.771 0 5.657 0 6.828-1.172S21 17.771 21 14v-2c0-3.771 0-5.657-1.172-6.828S16.771 4 13 4M3 10h18m-10 4h5m-8 0h.009M13 18H8m8 0h-.009"
                        />
                      </svg>
                    </i>
                    <span
                      data-i18n="news_date"
                      style="color: rgba(109, 109, 109, 1)"
                      >5 مارس 2026</span
                    >
                  </span>
                  <span class="readmore">
                    <a href="#">
                      <span
                        style="color: rgba(76, 92, 55, 1)"
                        data-i18n="read_more"
                        >اقرأ المزيد</span
                      >
                      <i
                        class="fa-solid fa-angle-left me-2 ms-1 arrow"
                        style="color: rgba(76, 92, 55, 1)"
                      ></i>
                    </a>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="text-center mt-5 fw-bold">
            <a href="#" class="px-3 py-2 fw-bold show-more-news">
              <span data-i18n="view_all_news" style="font-size: 18px"
                >عرض جميع الأخبار</span
              >
              <i
                class="fa-solid fa-angle-left me-2 arrow"
                style="font-size: 18px"
              ></i>
            </a>
          </div>
        </div>
      </section>

      <section class="content-section my-5">
        <div class="container position-relative">
          <img
            src="/assets/images/leaf_cutout.png"
            class="olive-branch branch-left-top-content-section"
            alt="Olive Branch"
          />
          <img
            src="/assets/images/leaf_cutout.png"
            class="olive-branch branch-right-bottom-content-section"
            alt="Olive Branch"
          />

          <div class="text-center mb-5">
            <h1
              class="creators-title"
              style="font-size: 42px; font-weight: bolder"
            >
              <span data-i18n="creators_title_main">صُناع المحتوى</span>
              <span class="who-us" data-i18n="at_sawt">في صوت</span>
            </h1>
            <h4
              class="font-24 creators-subtitle"
              style="color: rgba(72, 72, 72, 1); margin-top: 20px"
              data-i18n="creators_subtitle"
            >
              تعرف على صُنّاع المحتوى في صوت، حيث كل فكرة إلها صوت، وكل مبدع إله
              حكاية.
            </h4>
          </div>
          <div class="owl-carousel creators-carousel2">
            <!-- Card 1 -->
            <div class="item">
              <a href="#" class="text-decoration-none">
                <div class="main-container">
                  <div class="the-card">
                    <div
                      class="face front-face-img w-100 h-100 overflow-hidden text-white"
                    >
                      <div class="arrowDiv">
                        <span class="followers" data-i18n="creator_followers">
                          31.4K متابع
                        </span>
                      </div>

                      <div class="d-flex flex-column align-items-center pt-2">
                        <div
                          class="img-circle rounded-circle p-2 mb-3 d-flex justify-content-center align-items-center"
                        >
                          <img
                            class="rounded-circle object-fit-cover"
                            style="width: 95px; height: 95px"
                            src="/assets/images/محمود زعيتر 2.png"
                            alt="محمود زعيتر"
                          />
                        </div>
                        <div
                          class="name-tag text-center mb-1"
                          data-i18n="creator_name"
                        >
                          محمود عبدالله زعيتر
                        </div>
                        <div
                          class="job-tag p-2 text-center"
                          data-i18n="creator_role"
                        >
                          ممثل مسرحية
                        </div>
                      </div>

                      <div class="hover-overlay">
                        <h4
                          class="hover-title"
                          data-i18n="creator_overlay_title"
                        >
                          تجربتي مع صوت
                        </h4>
                        <p class="hover-desc" data-i18n="creator_quote">
                          تجربتي مع صوت كانت مختلفة، أخيراً لقيت مكان بيفهمني
                          كمبدع ....
                        </p>
                        <span class="hover-arrow">
                          <i class="fa-solid fa-arrow-up"></i>
                        </span>
                      </div>
                    </div>

                  </div>
                </div>
              </a>
            </div>

            <!-- Card 2 -->
            <div class="item">
              <a href="#" class="text-decoration-none">
                <div class="main-container">
                  <div class="the-card position-relative">
                    <div
                      class="face front-face-img w-100 h-100 overflow-hidden text-white"
                    >
                      <div class="arrowDiv">
                        <span class="followers" data-i18n="creator_followers">
                          31.4K متابع
                        </span>
                      </div>

                      <div class="d-flex flex-column align-items-center pt-2">
                        <div
                          class="img-circle rounded-circle p-2 mb-3 d-flex justify-content-center align-items-center"
                        >
                          <img
                            class="rounded-circle object-fit-cover"
                            style="width: 95px; height: 95px"
                            src="/assets/images/محمود زعيتر 2.png"
                            alt="محمود زعيتر"
                          />
                        </div>
                        <div
                          class="name-tag text-center mb-1"
                          data-i18n="creator_name"
                        >
                          محمود عبدالله زعيتر
                        </div>
                        <div
                          class="job-tag p-2 text-center"
                          data-i18n="creator_role"
                        >
                          ممثل مسرحية
                        </div>
                      </div>

                      <div class="hover-overlay">
                        <h4
                          class="hover-title"
                          data-i18n="creator_overlay_title"
                        >
                          تجربتي مع صوت
                        </h4>
                        <p class="hover-desc" data-i18n="creator_quote">
                          تجربتي مع صوت كانت مختلفة، أخيراً لقيت مكان بيفهمني
                          كمبدع ....
                        </p>
                        <span class="hover-arrow">
                          <i class="fa-solid fa-arrow-up"></i>
                        </span>
                      </div>
                    </div>


                  </div>
                </div>
              </a>
            </div>

            <!-- Card 3 -->
            <div class="item">
              <a href="#" class="text-decoration-none">
                <div class="main-container">
                  <div class="the-card position-relative">
                    <div
                      class="face front-face-img w-100 h-100 overflow-hidden text-white"
                    >
                      <div class="arrowDiv">
                        <span class="followers" data-i18n="creator_followers">
                          31.4K متابع
                        </span>
                      </div>

                      <div class="d-flex flex-column align-items-center pt-2">
                        <div
                          class="img-circle rounded-circle p-2 mb-3 d-flex justify-content-center align-items-center"
                        >
                          <img
                            class="rounded-circle object-fit-cover"
                            style="width: 95px; height: 95px"
                            src="/assets/images/محمود زعيتر 2.png"
                            alt="محمود زعيتر"
                          />
                        </div>
                        <div
                          class="name-tag text-center mb-1"
                          data-i18n="creator_name"
                        >
                          محمود عبدالله زعيتر
                        </div>
                        <div
                          class="job-tag p-2 text-center"
                          data-i18n="creator_role"
                        >
                          ممثل مسرحية
                        </div>
                      </div>

                      <div class="hover-overlay">
                        <h4
                          class="hover-title"
                          data-i18n="creator_overlay_title"
                        >
                          تجربتي مع صوت
                        </h4>
                        <p class="hover-desc" data-i18n="creator_quote">
                          تجربتي مع صوت كانت مختلفة، أخيراً لقيت مكان بيفهمني
                          كمبدع ....
                        </p>
                        <span class="hover-arrow">
                          <i class="fa-solid fa-arrow-up"></i>
                        </span>
                      </div>
                    </div>


                  </div>
                </div>
              </a>
            </div>

            <!-- Card 4 -->
            <div class="item">
              <a href="#" class="text-decoration-none">
                <div class="main-container">
                  <div class="the-card position-relative">
                    <div
                      class="face front-face-img w-100 h-100 overflow-hidden text-white"
                    >
                      <div class="arrowDiv">
                        <span class="followers" data-i18n="creator_followers">
                          31.4K متابع
                        </span>
                      </div>

                      <div class="d-flex flex-column align-items-center pt-2">
                        <div
                          class="img-circle rounded-circle p-2 mb-3 d-flex justify-content-center align-items-center"
                        >
                          <img
                            class="rounded-circle object-fit-cover"
                            style="width: 95px; height: 95px"
                            src="/assets/images/محمود زعيتر 2.png"
                            alt="محمود زعيتر"
                          />
                        </div>
                        <div
                          class="name-tag text-center mb-1"
                          data-i18n="creator_name"
                        >
                          محمود عبدالله زعيتر
                        </div>
                        <div
                          class="job-tag p-2 text-center"
                          data-i18n="creator_role"
                        >
                          ممثل مسرحية
                        </div>
                      </div>

                      <div class="hover-overlay">
                        <h4
                          class="hover-title"
                          data-i18n="creator_overlay_title"
                        >
                          تجربتي مع صوت
                        </h4>
                        <p class="hover-desc" data-i18n="creator_quote">
                          تجربتي مع صوت كانت مختلفة، أخيراً لقيت مكان بيفهمني
                          كمبدع ....
                        </p>
                        <span class="hover-arrow">
                          <i class="fa-solid fa-arrow-up"></i>
                        </span>
                      </div>
                    </div>

                  </div>
                </div>
              </a>
            </div>

            <!-- Card 5 -->
            <div class="item">
              <a href="#" class="text-decoration-none">
                <div class="main-container">
                  <div class="the-card position-relative">
                    <div
                      class="face front-face-img w-100 h-100 overflow-hidden text-white"
                    >
                      <div class="arrowDiv">
                        <span class="followers" data-i18n="creator_followers">
                          31.4K متابع
                        </span>
                      </div>

                      <div class="d-flex flex-column align-items-center pt-2">
                        <div
                          class="img-circle rounded-circle p-2 mb-3 d-flex justify-content-center align-items-center"
                        >
                          <img
                            class="rounded-circle object-fit-cover"
                            style="width: 95px; height: 95px"
                            src="/assets/images/محمود زعيتر 2.png"
                            alt="محمود زعيتر"
                          />
                        </div>
                        <div
                          class="name-tag text-center mb-1"
                          data-i18n="creator_name"
                        >
                          محمود عبدالله زعيتر
                        </div>
                        <div
                          class="job-tag p-2 text-center"
                          data-i18n="creator_role"
                        >
                          ممثل مسرحية
                        </div>
                      </div>

                      <div class="hover-overlay">
                        <h4
                          class="hover-title"
                          data-i18n="creator_overlay_title"
                        >
                          تجربتي مع صوت
                        </h4>
                        <p class="hover-desc" data-i18n="creator_quote">
                          تجربتي مع صوت كانت مختلفة، أخيراً لقيت مكان بيفهمني
                          كمبدع ....
                        </p>
                        <span class="hover-arrow">
                          <i class="fa-solid fa-arrow-up"></i>
                        </span>
                      </div>
                    </div>

                  </div>
                </div>
              </a>
            </div>
          </div>

          <div class="text-center">
            <a href="#" class="px-4 py-2 fw-bold show-more-news">
              <span data-i18n="view_all">عرض الكل</span>
              <i class="fa-solid fa-angle-left me-2 arrow"></i>
            </a>
          </div>
        </div>
      </section>

      <section class="platform-sections py-5 text-center mb-2">
        <div class="container">
          <div class="header-content mb-3">
            <h2 class="text-black fw-bold" style="font-size: 42px !important">
              <span data-i18n="platform_title_pre">أقسام</span>
              <span
                class="who-us"
                style="font-size: 45px !important"
                data-i18n="platform_title_highlight"
                >المنصة</span
              >
            </h2>
            <p
              class="font-24"
              style="color: rgba(72, 72, 72, 1)"
              data-i18n="creators_subtitle"
            >
              تعرف على صُنّاع المحتوى في صوت، حيث كل فكرة إلها صوت، وكل مبدع إله
              حكاية.
            </p>
          </div>

          <div class="row g-4 mt-2 justify-content-center">
            <div class="col-md-4">
              <div class="platform-card">
                <div class="image-container">
                  <img
                    src="/assets/images/Rectangle 592.png"
                    alt="منصة المحتوى"
                    class="img-fluid"
                  />
                  <div class="up-center-icon">
                    <div class="center-img">
                      <img
                        class="w-100 h-100"
                        src="/assets/images/شعار الحاضنة.png"
                        alt="شعار الحاضنة"
                      />
                    </div>
                  </div>
                </div>
                <div class="card-content mt-4">
                  <h4
                    class="text-black fw-bold"
                    data-i18n="platform_card1_title"
                  >
                    منصة المحتوى
                  </h4>
                  <p data-i18n="platform_card1_desc">
                    مكتبة غنية بالفيديوهات والقصص الإنسانية التي تروي واقع غزة
                    بكرامة واحترافية.
                  </p>

                  <div class="stats d-flex justify-content-start gap-3 mb-2">
                    <span
                      class="text-light-muted lh-lg"
                      style="color: rgba(127, 127, 127, 1)"
                    >
                      <i
                        ><svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="1.2em"
                          height="1.2em"
                          viewBox="0 0 24 24"
                        >
                          <path d="M0 0h24v24H0z" fill="none" />
                          <g
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                          >
                            <path
                              d="M21.544 11.045c.304.426.456.64.456.955c0 .316-.152.529-.456.955C20.178 14.871 16.689 19 12 19c-4.69 0-8.178-4.13-9.544-6.045C2.152 12.529 2 12.315 2 12c0-.316.152-.529.456-.955C3.822 9.129 7.311 5 12 5c4.69 0 8.178 4.13 9.544 6.045Z"
                            />
                            <path d="M15 12a3 3 0 1 0-6 0a3 3 0 0 0 6 0Z" />
                          </g>
                        </svg>
                      </i>
                      <span data-i18n="stat_views_30m">+30 مليون مشاهدة</span>
                    </span>

                    <span
                      class="text-light-muted lh-lg"
                      style="color: rgba(127, 127, 127, 1)"
                    >
                      <i
                        ><svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="1.2em"
                          height="1.2em"
                          viewBox="0 0 24 24"
                        >
                          <path d="M0 0h24v24H0z" fill="none" />
                          <g
                            fill="none"
                            stroke="currentColor"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                          >
                            <path
                              stroke-linecap="round"
                              d="M17.7 21.335c-1.172.165-2.7.165-4.75.165h-1.9c-4.03 0-6.046 0-7.298-1.252S2.5 16.98 2.5 12.95v-1.9c0-4.03 0-6.046 1.252-7.298S7.02 2.5 11.05 2.5h1.9c4.03 0 6.046 0 7.298 1.252S21.5 7.019 21.5 11.05v1.9c0 1.208 0 2.235-.034 3.115c-.027.705-.04 1.057-.307 1.19c-.267.13-.566-.08-1.163-.503L18.65 15.8"
                            />
                            <path
                              d="M14.945 12.395c-.176.627-1.012 1.07-2.682 1.955c-1.615.856-2.422 1.285-3.073 1.113a1.66 1.66 0 0 1-.712-.393C8 14.62 8 13.746 8 12s0-2.62.478-3.07c.198-.186.443-.321.712-.392c.65-.173 1.458.256 3.073 1.112c1.67.886 2.506 1.329 2.682 1.955c.073.259.073.531 0 .79Z"
                            />
                          </g>
                        </svg>
                      </i>
                      <span data-i18n="stat_clips_100">+100 مقطع</span>
                    </span>
                  </div>

                  <a href="#" class="read-more-btn text-white">
                    <span data-i18n="read_more">اقرأ المزيد</span>
                    <span class="arrow"
                      ><i class="fa-solid fa-angle-left"></i
                    ></span>
                  </a>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="platform-card">
                <div class="image-container">
                  <img
                    src="/assets/images/Rectangle 592.png"
                    alt="منصة المحتوى"
                    class="img-fluid"
                  />
                  <div class="up-center-icon">
                    <div class="center-img">
                      <img
                        class="w-100 h-100"
                        src="/assets/images/شعار الحاضنة.png"
                        alt="شعار الحاضنة"
                      />
                    </div>
                  </div>
                </div>
                <div class="card-content mt-4">
                  <h4
                    class="text-black fw-bold"
                    data-i18n="platform_card2_title"
                  >
                    حاضنة صوت
                  </h4>
                  <p data-i18n="platform_card2_desc">
                    برامج تدريبية متخصصة لتطوير مهارات صناع المحتوى وتمكينهم من
                    الإبداع والتميز.
                  </p>

                  <div class="stats d-flex justify-content-start gap-3 mb-2">
                    <span
                      class="text-light-muted lh-lg"
                      style="color: rgba(127, 127, 127, 1)"
                    >
                      <i
                        ><svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="1.2em"
                          height="1.2em"
                          viewBox="0 0 24 24"
                        >
                          <path d="M0 0h24v24H0z" fill="none" />
                          <g
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                          >
                            <path
                              d="M21.544 11.045c.304.426.456.64.456.955c0 .316-.152.529-.456.955C20.178 14.871 16.689 19 12 19c-4.69 0-8.178-4.13-9.544-6.045C2.152 12.529 2 12.315 2 12c0-.316.152-.529.456-.955C3.822 9.129 7.311 5 12 5c4.69 0 8.178 4.13 9.544 6.045Z"
                            />
                            <path d="M15 12a3 3 0 1 0-6 0a3 3 0 0 0 6 0Z" />
                          </g>
                        </svg>
                      </i>
                      <span data-i18n="stat_views_30m">+30 مليون مشاهدة</span>
                    </span>

                    <span
                      class="text-light-muted lh-lg"
                      style="color: rgba(127, 127, 127, 1)"
                    >
                      <i
                        ><svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="1.2em"
                          height="1.2em"
                          viewBox="0 0 24 24"
                        >
                          <path d="M0 0h24v24H0z" fill="none" />
                          <g
                            fill="none"
                            stroke="currentColor"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                          >
                            <path
                              stroke-linecap="round"
                              d="M17.7 21.335c-1.172.165-2.7.165-4.75.165h-1.9c-4.03 0-6.046 0-7.298-1.252S2.5 16.98 2.5 12.95v-1.9c0-4.03 0-6.046 1.252-7.298S7.02 2.5 11.05 2.5h1.9c4.03 0 6.046 0 7.298 1.252S21.5 7.019 21.5 11.05v1.9c0 1.208 0 2.235-.034 3.115c-.027.705-.04 1.057-.307 1.19c-.267.13-.566-.08-1.163-.503L18.65 15.8"
                            />
                            <path
                              d="M14.945 12.395c-.176.627-1.012 1.07-2.682 1.955c-1.615.856-2.422 1.285-3.073 1.113a1.66 1.66 0 0 1-.712-.393C8 14.62 8 13.746 8 12s0-2.62.478-3.07c.198-.186.443-.321.712-.392c.65-.173 1.458.256 3.073 1.112c1.67.886 2.506 1.329 2.682 1.955c.073.259.073.531 0 .79Z"
                            />
                          </g>
                        </svg>
                      </i>
                      <span data-i18n="stat_clips_100">+100 مقطع</span>
                    </span>
                  </div>

                  <a href="#" class="read-more-btn text-white">
                    <span data-i18n="read_more">اقرأ المزيد</span>
                    <span class="arrow"
                      ><i class="fa-solid fa-angle-left"></i
                    ></span>
                  </a>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="platform-card">
                <div class="image-container">
                  <img
                    src="/assets/images/Rectangle 592.png"
                    alt="منصة المحتوى"
                    class="img-fluid"
                  />
                  <div class="up-center-icon">
                    <div class="center-img">
                      <img
                        class="w-100 h-100"
                        src="/assets/images/شعار الحاضنة.png"
                        alt="شعار الحاضنة"
                      />
                    </div>
                  </div>
                </div>
                <div class="card-content mt-4">
                  <h4
                    class="text-black fw-bold"
                    data-i18n="platform_card3_title"
                  >
                    صوت ميديا
                  </h4>
                  <p data-i18n="platform_card3_desc">
                    شركة إنتاج إعلامي احترافية تقدم خدمات متكاملة من الكتابة إلى
                    التسويق.
                  </p>

                  <div class="stats d-flex justify-content-start gap-3 mb-2">
                    <span
                      class="text-light-muted lh-lg"
                      style="color: rgba(127, 127, 127, 1)"
                    >
                      <i
                        ><svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="1.2em"
                          height="1.2em"
                          viewBox="0 0 24 24"
                        >
                          <path d="M0 0h24v24H0z" fill="none" />
                          <g
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                          >
                            <path
                              d="M16.5 20v-2.03c0-1.242-.56-2.46-1.69-2.975C13.431 14.366 11.778 14 10 14s-3.431.366-4.81.995c-1.13.515-1.69 1.733-1.69 2.975V20m17 .001v-2.03c0-1.242-.56-2.46-1.69-2.975q-.39-.18-.81-.328"
                            />
                            <circle cx="10" cy="7.5" r="3.5" />
                            <path d="M15 4.145a3.502 3.502 0 0 1 0 6.71" />
                          </g>
                        </svg>
                      </i>
                      <span data-i18n="stat_clients_100">+100 عميل راض</span>
                    </span>

                    <span
                      class="text-light-muted lh-lg"
                      style="color: rgba(127, 127, 127, 1)"
                    >
                      <i>
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="1.2em"
                          height="1.2em"
                          viewBox="0 0 24 24"
                        >
                          <path d="M0 0h24v24H0z" fill="none" />
                          <path
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M5.333 3c2.46-.003 4.836.887 6.667 2.5V21a10.07 10.07 0 0 0-6.667-2.5c-1.562 0-2.343 0-2.688-.22a1.16 1.16 0 0 1-.424-.425C2 17.51 2 16.895 2 15.663v-9.26c0-1.428 0-2.141.549-2.72c.548-.579 1.11-.609 2.234-.668Q5.056 3 5.333 3m13.334 0A10.07 10.07 0 0 0 12 5.5V21a10.07 10.07 0 0 1 6.667-2.5c1.562 0 2.343 0 2.688-.22c.207-.133.291-.218.424-.425c.221-.345.221-.96.221-2.192v-9.26c0-1.428 0-2.141-.549-2.72s-1.11-.609-2.234-.668Q18.944 3 18.667 3"
                          />
                        </svg>
                      </i>
                      <span data-i18n="stat_projects_done">مشاريع المنجزة</span>
                    </span>
                  </div>

                  <a href="#" class="read-more-btn text-white">
                    <span data-i18n="read_more">اقرأ المزيد</span>
                    <span class="arrow"
                      ><i class="fa-solid fa-angle-left"></i
                    ></span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="mt-3">
        <div class="partners-section text-center mb-5">
          <h1 class="partners-title fw-bold" style="font-size: 42px">
            <span data-i18n="partners_title_main">شركاؤنا</span>
            <span class="partners-highlight who-us" data-i18n="at_sawt"
              >في صوت</span
            >
          </h1>
          <p class="partners-description font-24" data-i18n="creators_subtitle">
            تعرف على صُنّاع المحتوى في صوت، حيث كل فكرة إلها صوت، وكل مبدع إله
            حكاية.
          </p>
        </div>
        <div class="marquee">
          <div class="marquee-group">
            <img src="/assets/images/صوت 8.png" alt="sout" />
            <img src="/assets/images/صوت 8.png" alt="sout" />
            <img src="/assets/images/صوت 8.png" alt="sout" />
            <img src="/assets/images/صوت 8.png" alt="sout" />
            <img src="/assets/images/صوت 8.png" alt="sout" />
          </div>
          <div class="marquee-group" aria-hidden="true">
            <img src="/assets/images/صوت 8.png" alt="sout" />
            <img src="/assets/images/صوت 8.png" alt="sout" />
            <img src="/assets/images/صوت 8.png" alt="sout" />
            <img src="/assets/images/صوت 8.png" alt="sout" />
            <img src="/assets/images/صوت 8.png" alt="sout" />
          </div>
        </div>
        <!-- <div class="text-center mt-3" style="color: white">
          <a
            href="#"
            class="btn btn-dark rounded-pill px-4 py-2 text-white fw-bold"
          >
            <span data-i18n="be_partner">كن شريكاً لصوت</span>
            <i class="fa-solid fa-arrow-left me-2"></i>
          </a>
        </div> -->
      </section>

      <!-- ===================== Real Stories (قصص من الواقع) ===================== -->
      <section class="real-stories-section py-5">
        <div class="container">
          <div class="row g-4 align-items-center">
            <!-- Slider column -->
            <div class="col-lg-7 order-2 order-lg-2">
              <div class="owl-carousel real-stories-carousel">
                <!-- Card 1 — Tea -->
                <div class="item">
                  <div class="rs-card">
                    <img
                      class="rs-card-bg"
                      src="/assets/images/tea.png"
                      alt=""
                    />
                    <div class="rs-card-info">
                      <div class="rs-card-text">
                        <span class="rs-badge" data-i18n="rs_badge"
                          >قصة نجاح</span
                        >
                        <h5 class="rs-card-title" data-i18n="rs_card1_title">
                          أغلي كاسة شاي
                        </h5>
                        <p class="rs-card-desc" data-i18n="rs_card_desc">
                          من غزة الى الأردن وأمل لايمشي مجددا
                        </p>
                        <p class="rs-card-full" data-i18n="rs_card1_full">
                          من قلب غزة المحاصرة، حوّل صانع المحتوى كوب الشاي
                          البسيط إلى رمزٍ للصمود وسط الحصار. التقطت منصة صوت
                          حكايته وأوصلتها إلى العالم، لتتحوّل كاسة شاي إلى رسالة
                          أملٍ وإصرار.
                        </p>
                      </div>
                      <a
                        href="#"
                        class="rs-arrow"
                        aria-label="عرض القصة"
                        data-i18n-title="rs_view_story"
                      >
                        <i class="fa-solid fa-arrow-left"></i>
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Card 2 — Samir -->
                <div class="item">
                  <div class="rs-card">
                    <img
                      class="rs-card-bg"
                      src="/assets/images/boy.png"
                      alt=""
                    />
                    <div class="rs-card-info">
                      <div class="rs-card-text">
                        <span class="rs-badge" data-i18n="rs_badge"
                          >قصة نجاح</span
                        >
                        <h5 class="rs-card-title" data-i18n="rs_card2_title">
                          سمير البطل
                        </h5>
                        <p class="rs-card-desc" data-i18n="rs_card_desc">
                          من غزة الى الأردن وأمل لايمشي مجددا
                        </p>
                        <p class="rs-card-full" data-i18n="rs_card2_full">
                          في وسط دمار غزة، اختُطف صانع المحتوى سمير وأُصيبت يده
                          بوحشية، واضطر إلى الهجرة إلى الأردن بحثاً عن الأمان.
                          منصة صوت التقطت صورته ونقلت قصته للعالم، فصار صوته
                          أعلى من القنابل وحمل رسالة الأمل لآلاف الفلسطينيين.
                        </p>
                      </div>
                      <a
                        href="#"
                        class="rs-arrow"
                        aria-label="عرض القصة"
                        data-i18n-title="rs_view_story"
                      >
                        <i class="fa-solid fa-arrow-left"></i>
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Card 3 — placeholder (duplicate until real poster is ready) -->
                <div class="item">
                  <div class="rs-card">
                    <img
                      class="rs-card-bg"
                      src="/assets/images/tea.png"
                      alt=""
                    />
                    <div class="rs-card-info">
                      <div class="rs-card-text">
                        <span class="rs-badge" data-i18n="rs_badge"
                          >قصة نجاح</span
                        >
                        <h5 class="rs-card-title" data-i18n="rs_card1_title">
                          أغلي كاسة شاي
                        </h5>
                        <p class="rs-card-desc" data-i18n="rs_card_desc">
                          من غزة الى الأردن وأمل لايمشي مجددا
                        </p>
                        <p class="rs-card-full" data-i18n="rs_card1_full">
                          من قلب غزة المحاصرة، حوّل صانع المحتوى كوب الشاي
                          البسيط إلى رمزٍ للصمود وسط الحصار. التقطت منصة صوت
                          حكايته وأوصلتها إلى العالم، لتتحوّل كاسة شاي إلى رسالة
                          أملٍ وإصرار.
                        </p>
                      </div>
                      <a
                        href="#"
                        class="rs-arrow"
                        aria-label="عرض القصة"
                        data-i18n-title="rs_view_story"
                      >
                        <i class="fa-solid fa-arrow-left"></i>
                      </a>
                    </div>
                  </div>
                </div>

                <div class="item">
                  <div class="rs-card">
                    <img
                      class="rs-card-bg"
                      src="/assets/images/boy.png"
                      alt=""
                    />
                    <div class="rs-card-info">
                      <div class="rs-card-text">
                        <span class="rs-badge" data-i18n="rs_badge"
                          >قصة نجاح</span
                        >
                        <h5 class="rs-card-title" data-i18n="rs_card2_title">
                          سمير البطل
                        </h5>
                        <p class="rs-card-desc" data-i18n="rs_card_desc">
                          من غزة الى الأردن وأمل لايمشي مجددا
                        </p>
                        <p class="rs-card-full" data-i18n="rs_card2_full">
                          في وسط دمار غزة، اختُطف صانع المحتوى سمير وأُصيبت يده
                          بوحشية، واضطر إلى الهجرة إلى الأردن بحثاً عن الأمان.
                          منصة صوت التقطت صورته ونقلت قصته للعالم، فصار صوته
                          أعلى من القنابل وحمل رسالة الأمل لآلاف الفلسطينيين.
                        </p>
                      </div>
                      <a
                        href="#"
                        class="rs-arrow"
                        aria-label="عرض القصة"
                        data-i18n-title="rs_view_story"
                      >
                        <i class="fa-solid fa-arrow-left"></i>
                      </a>
                    </div>
                  </div>
                </div>

                <div class="item">
                  <div class="rs-card">
                    <img
                      class="rs-card-bg"
                      src="/assets/images/tea.png"
                      alt=""
                    />
                    <div class="rs-card-info">
                      <div class="rs-card-text">
                        <span class="rs-badge" data-i18n="rs_badge"
                          >قصة نجاح</span
                        >
                        <h5 class="rs-card-title" data-i18n="rs_card1_title">
                          أغلي كاسة شاي
                        </h5>
                        <p class="rs-card-desc" data-i18n="rs_card_desc">
                          من غزة الى الأردن وأمل لايمشي مجددا
                        </p>
                        <p class="rs-card-full" data-i18n="rs_card1_full">
                          من قلب غزة المحاصرة، حوّل صانع المحتوى كوب الشاي
                          البسيط إلى رمزٍ للصمود وسط الحصار. التقطت منصة صوت
                          حكايته وأوصلتها إلى العالم، لتتحوّل كاسة شاي إلى رسالة
                          أملٍ وإصرار.
                        </p>
                      </div>
                      <a
                        href="#"
                        class="rs-arrow"
                        aria-label="عرض القصة"
                        data-i18n-title="rs_view_story"
                      >
                        <i class="fa-solid fa-arrow-left"></i>
                      </a>
                    </div>
                  </div>
                </div>

                <div class="item">
                  <div class="rs-card">
                    <img
                      class="rs-card-bg"
                      src="/assets/images/boy.png"
                      alt=""
                    />
                    <div class="rs-card-info">
                      <div class="rs-card-text">
                        <span class="rs-badge" data-i18n="rs_badge"
                          >قصة نجاح</span
                        >
                        <h5 class="rs-card-title" data-i18n="rs_card2_title">
                          سمير البطل
                        </h5>
                        <p class="rs-card-desc" data-i18n="rs_card_desc">
                          من غزة الى الأردن وأمل لايمشي مجددا
                        </p>
                        <p class="rs-card-full" data-i18n="rs_card2_full">
                          في وسط دمار غزة، اختُطف صانع المحتوى سمير وأُصيبت يده
                          بوحشية، واضطر إلى الهجرة إلى الأردن بحثاً عن الأمان.
                          منصة صوت التقطت صورته ونقلت قصته للعالم، فصار صوته
                          أعلى من القنابل وحمل رسالة الأمل لآلاف الفلسطينيين.
                        </p>
                      </div>
                      <a
                        href="#"
                        class="rs-arrow"
                        aria-label="عرض القصة"
                        data-i18n-title="rs_view_story"
                      >
                        <i class="fa-solid fa-arrow-left"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Text + comment column -->
            <div class="col-lg-5 order-1 order-lg-1">
              <div class="rs-intro">
                <h2 class="rs-title fw-bold">
                  <span>قصص</span>
                  <span class="rs-title-word">من الواقع</span>
                </h2>
                <p class="rs-desc" data-i18n="realstories_desc">
                  كلنا نملك قصة تستحق أن تُروى. في هذا القسم، نضع مساحة لك
                  لتشارك قصتك الحقيقية. سواء كانت قصة نجاح، تحدي، إبداع، أو
                  تجربة حياتية مؤثرة.
                </p>
                <div class="rs-count">
                  <span class="rs-count-icon">
                    <i
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="1.2em"
                        height="1.2em"
                        viewBox="0 0 24 24"
                      >
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          d="M5.333 3c2.46-.003 4.836.887 6.667 2.5V21a10.07 10.07 0 0 0-6.667-2.5c-1.562 0-2.343 0-2.688-.22a1.16 1.16 0 0 1-.424-.425C2 17.51 2 16.895 2 15.663v-9.26c0-1.428 0-2.141.549-2.72c.548-.579 1.11-.609 2.234-.668Q5.056 3 5.333 3m13.334 0A10.07 10.07 0 0 0 12 5.5V21a10.07 10.07 0 0 1 6.667-2.5c1.562 0 2.343 0 2.688-.22c.207-.133.291-.218.424-.425c.221-.345.221-.96.221-2.192v-9.26c0-1.428 0-2.141-.549-2.72s-1.11-.609-2.234-.668Q18.944 3 18.667 3"
                        />
                      </svg>
                    </i>
                  </span>
                  <span class="rs-count-text" data-i18n="realstories_count"
                    >+100 قصة واقعية نقلتها صوت الى العالم</span
                  >
                </div>
                <form class="rs-comment-box" onsubmit="return false;">
                  <textarea
                    class="rs-input"
                    rows="4"
                    placeholder="شاركنا قصتك"
                    data-i18n-placeholder="realstories_input_placeholder"
                  ></textarea>
                  <button type="submit" class="rs-send" aria-label="إرسال">
                    <i
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="1.5em"
                        height="1.5em"
                        viewBox="0 0 24 24"
                      >
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          d="m9.498 15l7.5-7.5m-8.992.179l7.321-3.46c3.042-1.438 4.563-2.157 5.533-1.436s.693 2.365.138 5.652l-.954 5.662c-.363 2.149-.544 3.223-1.345 3.692s-1.842.109-3.923-.611l-6.365-2.202c-3.892-1.346-5.838-2.019-5.91-3.34c-.074-1.32 1.786-2.2 5.505-3.957M9.498 15.5v2.227c0 2.374 0 3.56.71 3.75s1.458-.798 2.954-2.773l.836-1.204"
                        />
                      </svg>
                    </i>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="team-section text-center">
        <div class="bg-scattered-photos">
          <img src="member1.jpg" class="pic-1" />
          <img src="member2.jpg" class="pic-2" />
          <img src="member3.jpg" class="pic-3" />
          <img src="member4.jpg" class="pic-4" />
        </div>
        <img
          src="/assets/images/leaf_cutout.png"
          class="olive-branch branch-left-top"
          alt="Olive Branch"
        />
        <img
          src="/assets/images/leaf_cutout.png"
          class="olive-branch branch-right-bottom"
          alt="Olive Branch"
        />

        <div class="container font-42">
          <h1 class="title">
            <span data-i18n="team_title_pre">أعضاء</span>
            <span>
              <span class="who-us" data-i18n="team_title_highlight"
                >فريقنا</span
              >
            </span>
          </h1>
          <p class="mb-5 describ-p" data-i18n="team_subtitle">
            تعرّف على فريق صوت، مبدعين يصنعون الفرق
          </p>
          <div class="owl-carousel owl-theme team-carousel">
            <div class="item">
              <div class="mic-container">
                <div class="member-photo-box"><img src="member1.jpg" /></div>
                <img src="/assets/images/مايك عوض 6.png" class="mic-frame" />
                <div class="member-name-tag" data-i18n="team_member_1">
                  هديل طافش
                </div>
              </div>
              <div class="btn-profile-wrapper">
                <a href="#" class="btn-view-profile">
                  <span data-i18n="view_profile">عرض الملف الشخصي</span>
                  <i class="fa-solid fa-angle-left"></i>
                </a>
              </div>
            </div>
            <div class="item">
              <div class="mic-container">
                <div class="member-photo-box"><img src="member2.jpg" /></div>
                <img src="/assets/images/مايك عوض 6.png" class="mic-frame" />
                <div class="member-name-tag" data-i18n="team_member_2">
                  محمد الأشقر
                </div>
              </div>
              <div class="btn-profile-wrapper">
                <a href="#" class="btn-view-profile">
                  <span data-i18n="view_profile">عرض الملف الشخصي</span>
                  <i class="fa-solid fa-angle-left"></i>
                </a>
              </div>
            </div>
            <div class="item">
              <div class="mic-container">
                <div class="member-photo-box"><img src="member3.jpg" /></div>
                <img src="/assets/images/مايك عوض 6.png" class="mic-frame" />
                <div class="member-name-tag" data-i18n="team_member_3">
                  محمود الصالح
                </div>
              </div>
              <div class="btn-profile-wrapper">
                <a href="#" class="btn-view-profile">
                  <span data-i18n="view_profile">عرض الملف الشخصي</span>
                  <i class="fa-solid fa-angle-left"></i>
                </a>
              </div>
            </div>
            <div class="item">
              <div class="mic-container">
                <div class="member-photo-box"><img src="member4.jpg" /></div>
                <img src="/assets/images/مايك عوض 6.png" class="mic-frame" />
                <div class="member-name-tag" data-i18n="team_member_4">
                  هديل طافش
                </div>
              </div>
              <div class="btn-profile-wrapper">
                <a href="#" class="btn-view-profile">
                  <span data-i18n="view_profile">عرض الملف الشخصي</span>
                  <i class="fa-solid fa-angle-left"></i>
                </a>
              </div>
            </div>
            <div class="item">
              <div class="mic-container">
                <div class="member-photo-box"><img src="member5.jpg" /></div>
                <img src="/assets/images/مايك عوض 6.png" class="mic-frame" />
                <div class="member-name-tag" data-i18n="team_member_5">
                  انس مليحة
                </div>
              </div>
              <div class="btn-profile-wrapper">
                <a href="#" class="btn-view-profile">
                  <span data-i18n="view_profile">عرض الملف الشخصي</span>
                  <i class="fa-solid fa-angle-left"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="join-us-section">
        <div class="join-us-banner">
          <img src="/assets/images/join-img.jpg" alt="" class="join-us-bg" />
          <div class="join-us-content text-center">
            <h2 class="join-us-title" data-i18n="join_creator_title">
              انضم إلينا كصانع محتوى
            </h2>
            <p class="join-us-desc" data-i18n="join_creator_desc">
صوت تجمع صناع المحتوى , كن صوت من لاصوت له  
            </p>
            <a href="#" class="btn btn-dark-green join-us-btn">
              <span data-i18n="join_creator_btn">طلب الانضمام</span>
              <i class="fa-solid fa-angle-left arrow"></i>
            </a>
          </div>
        </div>
      </section>

      <section class="stories-section reviews-section">
        <div class="reviews-inner">
          <div
            class="row g-4 align-items-center justify-content-center"
            style="
              background-color: rgba(237, 239, 235, 1);
              padding: 0px 30px 30px 30px;
              border-radius: 20px;
              margin-top: 10px;
            "
          >
            <div class="col-lg-3 col-md-12">
              <div class="reviews-intro">
                <h2 class="reviews-title fw-bold">
                  <span data-i18n="reviews_title_pre">أرائكم في</span>
                  <span
                    class="reviews-highlight"
                    data-i18n="reviews_title_highlight"
                    >المحتوى</span
                  >
                </h2>
                <p class="reviews-desc" data-i18n-html="reviews_desc_html">
                  نؤمن أن <span class="hl">رأيك</span> جزء أساسي من تطويرنا
                  وتحسين خدماتنا. شاركنا تجربتك واقتراحاتك وساعدنا على تقديم
                  تجربة أفضل تلبي احتياجاتك وتوقعاتك.
                </p>
              </div>
            </div>

            <div class="col-lg-3 col-md-6">
              <div class="review-reels reels-container" id="reelsContainer">
                @forelse ($reels as $i => $reel)
<div class="reel-item" data-index="{{ $i }}" data-reel-id="{{ $reel['id'] }}">
                  <div class="reel-media">
                    <video
                      src="{{ $reel['video_url'] }}"@if($reel['thumbnail']) poster="{{ $reel['thumbnail'] }}"@endif
                      loop
                      playsinline
                      onclick="toggleVideoPlay(this)"
                    ></video>
                    <div class="reel-overlay"></div>
                    <div class="reel-actions">
                      <span onclick="toggleSave(this)">
                        <i
                          ><svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="1.5em"
                            height="1.2em"
                            viewBox="0 0 24 24"
                          >
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path
                              fill="none"
                              stroke="currentColor"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.5"
                              d="M4 17.98V9.709c0-3.634 0-5.45 1.172-6.58S8.229 2 12 2s5.657 0 6.828 1.129C20 4.257 20 6.074 20 9.708v8.273c0 2.306 0 3.459-.773 3.871c-1.497.8-4.304-1.867-5.637-2.67c-.773-.465-1.16-.698-1.59-.698s-.817.233-1.59.698c-1.333.803-4.14 3.47-5.637 2.67C4 21.44 4 20.287 4 17.981"
                            />
                          </svg>
                        </i>
                      </span>
                      <span onclick="toggleLike(this)">
                        <i
                          ><svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="1.5em"
                            height="1.2em"
                            viewBox="0 0 24 24"
                          >
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path
                              fill="none"
                              stroke="currentColor"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.5"
                              d="M10.41 19.968C7.59 17.858 2 13.035 2 8.694C2 5.826 4.105 3.5 7 3.5c1.5 0 3 .5 5 2.5c2-2 3.5-2.5 5-2.5c2.895 0 5 2.326 5 5.194c0 4.34-5.59 9.164-8.41 11.274c-.95.71-2.23.71-3.18 0"
                            />
                          </svg>
                        </i>
                      </span>
                      <span onclick="shareVideo()">
                        <i
                          ><svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="1.5em"
                            height="1.5em"
                            viewBox="0 0 24 24"
                          >
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path
                              fill="currentColor"
                              d="M6.616 21q-.691 0-1.153-.462T5 19.385v-8.77q0-.69.463-1.152T6.616 9H8.23q.213 0 .357.143t.143.357t-.143.357T8.23 10H6.616q-.231 0-.424.192T6 10.616v8.769q0 .23.192.423t.423.192h10.77q.23 0 .423-.192t.192-.423v-8.77q0-.23-.192-.423T17.384 10H15.77q-.213 0-.357-.143T15.27 9.5t.143-.357T15.77 9h1.615q.691 0 1.153.463T19 10.616v8.769q0 .69-.463 1.153T17.385 21zm5.027-5.643Q11.5 15.214 11.5 15V4.614L9.754 6.36q-.146.146-.344.153q-.199.006-.364-.16q-.16-.164-.162-.353t.162-.354l2.388-2.388q.132-.131.268-.184q.137-.053.298-.053t.298.053t.268.184l2.388 2.388q.14.14.15.342q.01.2-.15.366q-.166.165-.357.165t-.357-.165l-1.74-1.74V15q0 .214-.143.357T12 15.5t-.357-.143"
                            /></svg
                        ></i>
                      </span>
                    </div>
                    <div class="reel-seekbar">
                      <span class="reel-time">0:00</span>
                      <div class="reel-progress">
                        <div class="reel-progress-fill"></div>
                      </div>
                    </div>
                    <div class="play-overlay" onclick="togglePlay(this)">
                      <i class="fa-solid fa-play"></i>
                    </div>
                  </div>
                  <div class="reel-caption">
                    <p class="reel-title" data-i18n="reel_title">
                      {{ \Illuminate\Support\Str::limit($reel['caption'] ?: 'ريل من إنستغرام', 45) }}
                    </p>
                    <span class="reel-views" data-i18n="reel_views"
                      >{{ $reel['likes'] }} إعجاب</span
                    >
                  </div>
                </div>
                                @empty
                <p class="text-center text-muted py-4" style="width:100%">لا يوجد ريلز للعرض حالياً.</p>
                @endforelse
              </div>
            </div>

            <div class="col-lg-6 col-md-6">
              <div class="comments-card reviews-comments">
                <div class="reviews-comments-head p-4">
                  <div class="comments-count-group">
                    <span class="comments-head-icon">
                      <i
                        ><svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="1.5em"
                          height="1.5em"
                          viewBox="0 0 24 24"
                        >
                          <path d="M0 0h24v24H0z" fill="none" />
                          <g
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-width="1.5"
                          >
                            <path
                              stroke-linejoin="round"
                              d="M8 13.5h8m-8-5h4"
                            />
                            <path
                              d="M6.099 19q-1.949-.192-2.927-1.172C2 16.657 2 14.771 2 11v-.5c0-3.771 0-5.657 1.172-6.828S6.229 2.5 10 2.5h4c3.771 0 5.657 0 6.828 1.172S22 6.729 22 10.5v.5c0 3.771 0 5.657-1.172 6.828S17.771 19 14 19c-.56.012-1.007.055-1.445.155c-1.199.276-2.309.89-3.405 1.424c-1.563.762-2.344 1.143-2.834.786c-.938-.698-.021-2.863.184-3.865"
                            />
                          </g>
                        </svg>
                      </i>
                    </span>
                    <span class="comments-count" data-i18n="comments_full_label"
                      >التعليقات (341)</span
                    >
                  </div>
                  <div class="tab-row">
                    <button
                      class="tab"
                      onclick="setTab(this, 'الأقدم')"
                      data-i18n="tab_oldest"
                    >
                      الأقدم
                    </button>
                    <button
                      class="tab active"
                      onclick="setTab(this, 'الأحدث')"
                      data-i18n="tab_newest"
                    >
                      الأحدث
                    </button>
                  </div>
                </div>
                <div class="comments-list" id="commentsList"></div>

                <div class="comment-input-row p-4">
                  <textarea
                    class="comment-input"
                    id="newComment"
                    rows="1"
                    placeholder="اترك تعليقك هنا..."
                    data-i18n-placeholder="comment_placeholder"
                    style="text-align: start"
                  ></textarea>
                  <button class="rs-send" onclick="addComment()">
                    <i>
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="1.4em"
                        height="1.2em"
                        viewBox="0 0 24 24"
                      >
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          d="m9.498 15l7.5-7.5m-8.992.179l7.321-3.46c3.042-1.438 4.563-2.157 5.533-1.436s.693 2.365.138 5.652l-.954 5.662c-.363 2.149-.544 3.223-1.345 3.692s-1.842.109-3.923-.611l-6.365-2.202c-3.892-1.346-5.838-2.019-5.91-3.34c-.074-1.32 1.786-2.2 5.505-3.957M9.498 15.5v2.227c0 2.374 0 3.56.71 3.75s1.458-.798 2.954-2.773l.836-1.204"
                        />
                      </svg>
                    </i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
@endsection

@push('scripts')
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
      integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p"
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"
      integrity="sha512-A7AYk1fGKX6S2SsHywmPkrnzTZHrgiVT7GcQkLGDe2ev0aWb8zejytzS8wjo7PGEXKqJOrjQ4oORtnimIRZBtw=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    ></script>
    <script
      src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"
      integrity="sha512-uURl+ZXMBrF4AwGaWmEetzrd+J5/8NRkWAvJx5sbPSSuOb0bZLqf+tOzniObO00BjHa/dD7gub9oCGMLPQHtQA=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    ></script>
    <script>
      // تعليقات كل ريل قادمة من إنستغرام (مفتاحها معرّف الريل)
      window.reelComments = <?php
        $reelComments = [];
        $reelCommentCounts = [];
        foreach (($reels ?? []) as $reel) {
            $id = (string) $reel['id'];
            $reelComments[$id] = $reel['comment_items'] ?? [];
            $reelCommentCounts[$id] = (int) ($reel['comments'] ?? 0);
        }
        echo json_encode($reelComments, JSON_UNESCAPED_UNICODE);
      ?>;
      window.reelCommentCounts = <?php echo json_encode($reelCommentCounts, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script>
      $(document).ready(function () {
        $(".creators-carousel").owlCarousel({
          loop: true,
          margin: 20,
          rtl: true, // مهم عشان العربي
          nav: true,
          dots: false,

          navText: [
            "<span class='arrow'>‹</span>",
            "<span class='arrow'>›</span>",
          ],

          responsive: {
            0: {
              items: 1,
            },
            600: {
              items: 2,
            },
            1000: {
              items: 3,
            },
          },
        });
      });
      $(document).ready(function () {
        $(".creators-carousel2").owlCarousel({
          loop: true,
          margin: 25,
          rtl: true, // مهم عشان العربي
          nav: true,
          dots: false,

          navText: [
            "<span class='arrow'>‹</span>",
            "<span class='arrow'>›</span>",
          ],

          responsive: {
            0: {
              items: 1,
            },
            600: {
              items: 2,
            },
            1100: {
              items: 3,
            },
            1300: {
              items: 4,
            },
            1500: {
              items: 5,
            },
          },
        });
      });

      $(document).ready(function () {
        $(".real-stories-carousel").owlCarousel({
          loop: true,
          margin: 20,
          rtl: true,
          nav: false,
          dots: true,
          responsive: {
            0: { items: 1 },
            768: { items: 2 },
          },
        });
      });

      $(document).ready(function () {
        var owl = $(".team-carousel");

        owl.owlCarousel({
          rtl: true,
          loop: true,
          margin: 20,
          nav: true,
          dots: false,
          navText: [
            "<i class='fas fa-chevron-right'></i>",
            "<i class='fas fa-chevron-left'></i>",
          ],
          responsive: {
            0: { items: 1 },
            600: { items: 2 },
            1000: { items: 4 },
          },
          onTranslated: highlightMiddle,
          onInitialized: highlightMiddle,
        });

        function highlightMiddle() {
          $(".team-carousel .owl-item").removeClass("center-highlight");
          var activeItems = $(".team-carousel .owl-item.active");
          if (activeItems.length === 4) {
            $(activeItems[1]).addClass("center-highlight");
            $(activeItems[2]).addClass("center-highlight");
          } else {
            activeItems.addClass("center-highlight");
          }
        }
      });
    </script>
@endpush
