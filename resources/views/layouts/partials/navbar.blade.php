<nav class="navbar navbar-expand-lg py-1">
  <div class="container bg-white shadow-sm py-1">
    <a class="navbar-brand" href="{{ url('/') }}" style="margin-right: 0 !important">
      <img src="{{ $logoUrl }}" alt="Sawt Logo" height="60" />
    </a>
    <button
      class="navbar-toggler"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#mainNav"
    >
      <span class="navbar-toggler-icon"></span>
    </button>
    <div
      class="collapse navbar-collapse flex-column flex-lg-row align-items-start align-items-lg-center"
      id="mainNav"
    >
      <ul class="navbar-nav mb-2 mb-lg-0 fw-bold" style="text-align: start">
        @forelse ($headerNavLinks as $hi => $link)
          @if (in_array($link['key'] ?? '', ['incubator', 'media'], true) && ($hi === 0 || ! in_array($headerNavLinks[$hi - 1]['key'] ?? '', ['incubator', 'media'], true)))
            <div class="v-divider d-none d-lg-block mx-3"></div>
          @endif
          <li class="nav-item ms-lg-3">
            <a
              class="nav-link font-16 {{ in_array($link['key'] ?? '', ['incubator', 'media'], true) ? 'nav-link-back font-color-green' : '' }} {{ ($activeNav ?? '') === ($link['key'] ?? '') ? 'active' : '' }}"
              href="{{ \App\Support\LayoutLinks::hrefForKey($link['key'] ?? null) }}"
              @if (in_array($link['key'] ?? '', ['incubator', 'media'], true)) style="color: rgba(76, 92, 55, 1) !important" @endif
              data-i18n="nav_custom_{{ $hi }}"
            >{{ $link['label_ar'] ?? '' }}</a>
          </li>
        @empty
          <li class="nav-item ms-lg-3">
            <a class="nav-link font-16 {{ ($activeNav ?? '') === 'home' ? 'active' : '' }}" href="{{ url('/') }}">الرئيسية</a>
          </li>
        @endforelse
      </ul>

      <div class="d-flex gap-2 nav-search-div">
        <div class="position-relative nav-search-div">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="1em"
            height="1em"
            viewBox="0 0 24 24"
            class="fa fa-search position-absolute top-50 end-0 translate-middle-y me-3"
          >
            <path d="M0 0h24v24H0z" fill="none" />
            <path
              fill="none"
              stroke="rgba(145, 145, 145, 1)"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="m17 17l4 4m-2-10a8 8 0 1 0-16 0a8 8 0 0 0 16 0"
            />
          </svg>
          <input
            type="text"
            class="form-control custom-placeholder py-2 search-input"
            placeholder="ابحث هنا..."
            data-i18n-placeholder="search_placeholder"
          />
        </div>
      </div>

      <div class="contact-info-nav small d-flex align-items-center gap-2">
        @auth
          <span class="fw-bold font-14" style="color: #38422a">{{ auth()->user()->name }}</span>
          <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="sign-in-btn border-0 bg-transparent p-0">
              <span style="cursor: pointer">تسجيل الخروج</span>
            </button>
          </form>
        @else
          <div class="register-btn">
            <a href="{{ route('register') }}" data-i18n="register_account">أنشئ حساب</a>
          </div>
          <div class="sign-in-btn">
            <a href="{{ route('login') }}" data-i18n="sign_in">تسجيل الدخول</a>
          </div>
        @endauth
      </div>
      <div class="searchDiv d-flex align-items-center gap-2">
        <button class="btn rounded-nav nav-bttn" type="button">
          <i class="ri-moon-line"></i>
        </button>
        <button class="btn rounded-nav language-btn nav-bttn" type="button" aria-label="toggle language">
          <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="3em" viewBox="0 0 24 24">
            <path d="M0 0h24v24H0z" fill="none" />
            <g fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10" />
              <path stroke-linejoin="round" d="M8 12c0 6 4 10 4 10s4-4 4-10s-4-10s-4 10s-4 4-4 10Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 15H3m18-6H3" />
            </g>
          </svg>
        </button>
      </div>
    </div>
  </div>
</nav>
