@php
    use App\Models\Setting;
    use Illuminate\Support\Facades\Storage;

    // مصدر واحد لإعدادات الـ SEO — يُستخدم في كل صفحات الموقع.
    // يمكن تمرير قيم خاصة بكل صفحة: seoTitle / seoDescription / seoKeywords
    $siteName = Setting::get('site_name', 'منصة صوت');

    $favRaw = Setting::get('site_favicon');
    $favicon = $favRaw ? Storage::disk('public')->url($favRaw) : '/assets/images/icon.png';

    $logoRaw = Setting::get('home_logo');
    $logoUrl = $logoRaw ? Storage::disk('public')->url($logoRaw) : '/assets/images/صوت 1.png';

    $ogRaw = Setting::get('og_image');
    $ogImg = $ogRaw ? Storage::disk('public')->url($ogRaw) : $logoUrl;

    $seoT = $seoTitle ?? Setting::get('meta_title', 'منصة صوت | نروي قصص غزة ونصنع جيلاً من المبدعين');
    $seoD = $seoDescription ?? Setting::get('meta_description', 'منصة صوت — مساحة فلسطينية للمبدعين في غزة: نروي القصص بكرامة، ندعم صنّاع المحتوى، ونبني مجتمعاً إبداعياً مؤثراً.');
    $seoK = $seoKeywords ?? Setting::get('meta_keywords', 'صوت, منصة صوت, غزة, فلسطين, صناع المحتوى, قصص غزة, ريلز');
@endphp
<title>{{ $seoT }}</title>
<meta name="description" content="{{ $seoD }}" />
<meta name="keywords" content="{{ $seoK }}" />
<link rel="icon" href="{{ $favicon }}" />
<link rel="shortcut icon" href="{{ $favicon }}" />
<link rel="apple-touch-icon" href="{{ $favicon }}" />
<link rel="canonical" href="{{ url()->current() }}" />

<!-- Open Graph (فيسبوك / واتساب / لينكدإن) -->
<meta property="og:type" content="website" />
<meta property="og:site_name" content="{{ $siteName }}" />
<meta property="og:title" content="{{ $seoT }}" />
<meta property="og:description" content="{{ $seoD }}" />
<meta property="og:image" content="{{ $ogImg }}" />
<meta property="og:url" content="{{ url()->current() }}" />

<!-- Twitter / X -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $seoT }}" />
<meta name="twitter:description" content="{{ $seoD }}" />
<meta name="twitter:image" content="{{ $ogImg }}" />
