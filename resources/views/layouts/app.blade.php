@php
    use App\Models\Setting;
    use App\Support\LayoutLinks;
    use App\Support\MediaUrl;

    $logoUrl = MediaUrl::make(Setting::get('home_logo'), '/assets/images/صوت 1.png');

    $contactPhone = Setting::get('contact_phone', '+972567247177');
    $contactEmail = Setting::get('contact_email', 'info@sawtgaza.com');
    $facebookUrl = Setting::get('facebook_url') ?: '#';
    $instagramUrl = Setting::get('instagram_url') ?: '#';
    $twitterUrl = Setting::get('twitter_url') ?: '#';
    $linkedinUrl = Setting::get('linkedin_url') ?: '#';
    $telegramUrl = Setting::get('telegram_url') ?: '#';

    $siteName = Setting::get('site_name', 'Sawt');
    $headerWrapperClass = $headerWrapperClass ?? 'main-header-wrapper';
    $headerWrapperStyle = $headerWrapperStyle ?? '';
    $activeNav = $activeNav ?? null;
    $i18nOverrides = $i18nOverrides ?? ['ar' => [], 'en' => []];

    $headerNavDefault = [
        ['key' => 'home', 'label_ar' => 'الرئيسية', 'label_en' => 'Home', 'is_visible' => true],
        ['key' => 'about', 'label_ar' => 'من نحن', 'label_en' => 'About Us', 'is_visible' => true],
        ['key' => 'content', 'label_ar' => 'محتوانا', 'label_en' => 'Our Content', 'is_visible' => true],
        ['key' => 'team', 'label_ar' => 'الفريق', 'label_en' => 'Team', 'is_visible' => true],
        ['key' => 'creators', 'label_ar' => 'صناع المحتوى', 'label_en' => 'Content Creators', 'is_visible' => true],
        ['key' => 'support', 'label_ar' => 'ادعم صوت', 'label_en' => 'Support Sawt', 'is_visible' => true],
        ['key' => 'incubator', 'label_ar' => 'حاضنة صوت', 'label_en' => 'Sawt Incubator', 'is_visible' => true],
        ['key' => 'media', 'label_ar' => 'صوت ميديا', 'label_en' => 'Sawt Media', 'is_visible' => true],
    ];
    $headerNavLinks = collect(LayoutLinks::visible(Setting::get('header_nav_links', $headerNavDefault) ?: $headerNavDefault))
        ->reject(fn (array $item) => in_array($item['key'] ?? '', LayoutLinks::NAV_EXCLUDED_KEYS, true))
        ->values()
        ->all();

    $footerLogoUrl = MediaUrl::make(Setting::get('footer_logo'), '/assets/images/صوت ابيض.png');

    $footerAboutAr = Setting::get('footer_about_ar', 'منصة صوت، تأسست لتكون مساحة للمبدعين، تجمع الحاضنة، صوت ميديا، والصوت نفسه، لتقديم محتوى ملهم وتجارب فريدة لكل من يسعى لصوته أن يُسمع.');
    $footerAboutEn = Setting::get('footer_about_en', '');
    $footerMainTitleAr = Setting::get('footer_main_title_ar', 'الأقسام الرئيسية');
    $footerMainTitleEn = Setting::get('footer_main_title_en', 'Main Sections');
    $footerMainDefault = [
        ['key' => 'home', 'label_ar' => 'الرئيسية', 'label_en' => 'Home', 'is_visible' => true],
        ['key' => 'about', 'label_ar' => 'من نحن', 'label_en' => 'About Us', 'is_visible' => true],
        ['key' => 'content', 'label_ar' => 'محتوانا', 'label_en' => 'Our Content', 'is_visible' => true],
        ['key' => 'team', 'label_ar' => 'الفريق', 'label_en' => 'Team', 'is_visible' => true],
        ['key' => 'creators', 'label_ar' => 'صناع المحتوى', 'label_en' => 'Content Creators', 'is_visible' => true],
        ['key' => 'incubator', 'label_ar' => 'حاضنة صوت', 'label_en' => 'Sawt Incubator', 'is_visible' => true],
        ['key' => 'media', 'label_ar' => 'صوت ميديا', 'label_en' => 'Sawt Media', 'is_visible' => true],
    ];
    $footerMainLinks = LayoutLinks::visible(Setting::get('footer_main_links', $footerMainDefault) ?: $footerMainDefault);
    $footerQuickTitleAr = Setting::get('footer_quick_title_ar', 'روابط سريعة');
    $footerQuickTitleEn = Setting::get('footer_quick_title_en', 'Quick Links');
    $footerQuickDefault = [
        ['key' => 'backstage', 'label_ar' => 'الكواليس', 'label_en' => 'Behind the Scenes', 'url' => '#', 'is_visible' => true],
        ['key' => 'media_kit', 'label_ar' => 'MEDIA KIT', 'label_en' => 'MEDIA KIT', 'url' => '#', 'is_visible' => true],
        ['key' => 'blog', 'label_ar' => 'المدونة', 'label_en' => 'Blog', 'url' => '#', 'is_visible' => true],
        ['key' => 'faq', 'label_ar' => 'الأسئلة الشائعة', 'label_en' => 'FAQs', 'url' => '#', 'is_visible' => true],
    ];
    $footerQuickLinks = LayoutLinks::visible(Setting::get('footer_quick_links', $footerQuickDefault) ?: $footerQuickDefault);
    $footerNewsletterTitleAr = Setting::get('footer_newsletter_title_ar', 'ابقَ على اطلاع');
    $footerNewsletterTitleEn = Setting::get('footer_newsletter_title_en', 'Stay Updated');
    $footerNewsletterDescAr = Setting::get('footer_newsletter_desc_ar', 'اشترك في نشرتنا الإخبارية ..');
    $footerNewsletterDescEn = Setting::get('footer_newsletter_desc_en', 'Subscribe to our newsletter..');
    $footerCopyrightAr = Setting::get('footer_copyright_ar', '© جميع الحقوق محفوظة. 2026');
    $footerCopyrightEn = Setting::get('footer_copyright_en', '© All rights reserved. 2026');
    $footerBrand = Setting::get('footer_brand', 'SAWTGAZA');

    if (filled($footerAboutAr)) {
        $i18nOverrides['ar']['footer_about'] = $footerAboutAr;
    }
    if (filled($footerAboutEn)) {
        $i18nOverrides['en']['footer_about'] = $footerAboutEn;
    }
    if (filled($footerMainTitleAr)) {
        $i18nOverrides['ar']['footer_main_sections'] = $footerMainTitleAr;
    }
    if (filled($footerMainTitleEn)) {
        $i18nOverrides['en']['footer_main_sections'] = $footerMainTitleEn;
    }
    if (filled($footerQuickTitleAr)) {
        $i18nOverrides['ar']['footer_quick_links'] = $footerQuickTitleAr;
    }
    if (filled($footerQuickTitleEn)) {
        $i18nOverrides['en']['footer_quick_links'] = $footerQuickTitleEn;
    }
    if (filled($footerNewsletterTitleAr)) {
        $i18nOverrides['ar']['footer_stay_updated'] = $footerNewsletterTitleAr;
    }
    if (filled($footerNewsletterTitleEn)) {
        $i18nOverrides['en']['footer_stay_updated'] = $footerNewsletterTitleEn;
    }
    if (filled($footerNewsletterDescAr)) {
        $i18nOverrides['ar']['footer_subscribe'] = $footerNewsletterDescAr;
    }
    if (filled($footerNewsletterDescEn)) {
        $i18nOverrides['en']['footer_subscribe'] = $footerNewsletterDescEn;
    }
    if (filled($footerCopyrightAr)) {
        $i18nOverrides['ar']['footer_copyright'] = $footerCopyrightAr;
    }
    if (filled($footerCopyrightEn)) {
        $i18nOverrides['en']['footer_copyright'] = $footerCopyrightEn;
    }
    if (filled($footerBrand)) {
        $i18nOverrides['ar']['footer_rights_brand'] = $footerBrand;
        $i18nOverrides['en']['footer_rights_brand'] = $footerBrand;
    }

    foreach ($headerNavLinks as $hi => $hLink) {
        if (filled($hLink['label_ar'] ?? null)) {
            $i18nOverrides['ar']['nav_custom_'.$hi] = $hLink['label_ar'];
        }
        if (filled($hLink['label_en'] ?? null)) {
            $i18nOverrides['en']['nav_custom_'.$hi] = $hLink['label_en'];
        }
    }
    foreach ($footerMainLinks as $fi => $fLink) {
        if (filled($fLink['label_ar'] ?? null)) {
            $i18nOverrides['ar']['footer_main_'.$fi] = $fLink['label_ar'];
        }
        if (filled($fLink['label_en'] ?? null)) {
            $i18nOverrides['en']['footer_main_'.$fi] = $fLink['label_en'];
        }
    }
    foreach ($footerQuickLinks as $qi => $qLink) {
        if (filled($qLink['label_ar'] ?? null)) {
            $i18nOverrides['ar']['footer_quick_'.$qi] = $qLink['label_ar'];
        }
        if (filled($qLink['label_en'] ?? null)) {
            $i18nOverrides['en']['footer_quick_'.$qi] = $qLink['label_en'];
        }
    }
@endphp
<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />
    <title>@yield('title', $siteName)</title>
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/icon.png" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
      integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css"
      integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css"
    />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="/assets/css/style.css" />

    @stack('styles')
  </head>

  <body>
    <header>
      <div class="<?php echo e($headerWrapperClass); ?> py-1" style="<?php echo e($headerWrapperStyle); ?>">
        @include('layouts.partials.topbar')
        @include('layouts.partials.navbar')
        @yield('header_extra')
      </div>
    </header>

    @yield('content')

    @include('layouts.partials.footer')

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
      integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF"
      crossorigin="anonymous"
    ></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="/assets/js/script.js"></script>
    <script>
      window.i18nOverrides = <?php echo json_encode($i18nOverrides ?? [], JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="/assets/js/translate.js"></script>

    @stack('scripts')
  </body>
</html>
