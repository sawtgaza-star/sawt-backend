<div class="container nav-face py-2 text-white border-bottom border-light border-opacity-25">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="social-links">
      <span
        class="font-18 ms-2"
        data-i18n="follow_us"
        style="color: rgba(127, 127, 127, 1); font-weight: bold; font-size: 16px"
      >وسائل التواصل الاجتماعي</span>
      <a href="{{ $linkedinUrl }}" class="text-white ms-2" target="_blank" rel="noopener">
        <i class="fab fa-linkedin-in font-18"></i>
      </a>
      <a href="{{ $facebookUrl }}" class="text-white ms-2" target="_blank" rel="noopener">
        <i class="fab fa-facebook-f font-18"></i>
      </a>
      <a href="{{ $telegramUrl }}" class="text-white ms-2" target="_blank" rel="noopener">
        <i class="fa-solid fa-paper-plane"></i>
      </a>
      <a href="{{ $twitterUrl }}" class="text-white ms-2" target="_blank" rel="noopener">
        <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" viewBox="0 0 24 24">
          <path d="M0 0h24v24H0z" fill="none" />
          <path
            fill="rgba(76, 92, 55, 1)"
            d="M19.57 4.488a.75.75 0 0 0-1.14-.976l-5.368 6.274l-5.224-5.938a1.8 1.8 0 0 0-1.357-.598H5.007c-.68 0-1.264.352-1.56.885a1.55 1.55 0 0 0 .204 1.795l6.286 7.147l-5.507 6.435a.75.75 0 1 0 1.14.976l5.368-6.274l5.224 5.938c.345.392.85.598 1.357.598h1.474c.681 0 1.264-.352 1.56-.885a1.55 1.55 0 0 0-.203-1.795l-6.287-7.146z"
          />
        </svg>
      </a>
      <a href="{{ $instagramUrl }}" class="text-white ms-2" target="_blank" rel="noopener">
        <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24" class="icon">
          <path d="M0 0h24v24H0z" fill="none" />
          <path
            fill="rgba(76, 92, 55, 1)"
            d="M9.367 2.25c-1.092 0-1.958 0-2.655.057c-.714.058-1.317.18-1.868.46a4.75 4.75 0 0 0-2.076 2.077c-.281.55-.403 1.154-.461 1.868c-.057.697-.057 1.563-.057 2.655v5.266c0 1.092 0 1.958.057 2.655c.058.714.18 1.317.46 1.869a4.75 4.75 0 0 0 2.077 2.075c.55.281 1.154.403 1.868.461c.697.057 1.563.057 2.655.057h5.266c1.092 0 1.958 0 2.655-.057c.714-.058 1.317-.18 1.869-.46a4.75 4.75 0 0 0 2.075-2.076c.281-.552.403-1.155.461-1.869c.057-.697.057-1.563.057-2.655V9.367c0-1.092 0-1.958-.057-2.655c-.058-.714-.18-1.317-.46-1.868a4.75 4.75 0 0 0-2.076-2.076c-.552-.281-1.155-.403-1.869-.461c-.697-.057-1.563-.057-2.655-.057zM16.25 6.5a.75.75 0 0 1 .75-.75h.5a.75.75 0 0 1 0 1.5H17a.75.75 0 0 1-.75-.75m-6.207 1.728a4.25 4.25 0 1 1 3.914 7.544a4.25 4.25 0 0 1-3.914-7.544"
          />
        </svg>
      </a>
    </div>

    <div class="contact-info small d-flex justify-content-center align-items-center gap-2">
      @auth
        <span class="fw-bold" style="color: rgba(76, 92, 55, 1)">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button type="submit" class="sign-in-btn border-0 bg-transparent p-0" style="cursor: pointer">
            تسجيل الخروج
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
  </div>
</div>
