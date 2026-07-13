@php
    use App\Models\Setting;
    use Illuminate\Support\Facades\Storage;

    $logoRaw = Setting::get('home_logo');
    $logoUrl = filled($logoRaw)
        ? (str_starts_with($logoRaw, '/') || str_starts_with($logoRaw, 'http')
            ? $logoRaw
            : Storage::disk('public')->url($logoRaw))
        : '/assets/images/صوت 1.png';

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
