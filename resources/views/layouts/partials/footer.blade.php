<footer class="footer-custom-bg pt-5 pb-4">
  <div class="footer-custom-width" style="width: 95%; margin: 0 auto">
    <div class="row g-4 text-end align-items-start">
      <div class="col-lg-3 col-md-6" style="text-align: start">
        <div class="footer-logo mb-3">
          <img src="{{ $footerLogoUrl }}" alt="صوت" width="100" />
        </div>
        <p class="lh-lg text-white font-16" data-i18n="footer_about">
          {{ $footerAboutAr }}
        </p>
      </div>

      <div class="col col-lg-3 col-md-6 text-white" style="text-align: start">
        <h5 class="fw-bold mb-4 text-white" data-i18n="footer_main_sections">{{ $footerMainTitleAr }}</h5>
        <div class="row">
          @php
            $mainMid = (int) ceil(count($footerMainLinks) / 2);
            $mainLeft = array_slice($footerMainLinks, 0, $mainMid);
            $mainRight = array_slice($footerMainLinks, $mainMid);
          @endphp
          <div class="col col-lg-6">
            <ul class="list-unstyled footer-links">
              @foreach ($mainLeft as $fi => $link)
                <li class="mb-4">
                  <a href="{{ \App\Support\LayoutLinks::hrefForKey($link['key'] ?? null) }}" class="text-white text-decoration-none small">
                    <span data-i18n="footer_main_{{ $fi }}">{{ $link['label_ar'] ?? '' }}</span>
                  </a>
                </li>
              @endforeach
            </ul>
          </div>
          <div class="col-lg-6 main-links">
            <ul class="list-unstyled p-0 footer-links">
              @foreach ($mainRight as $ri => $link)
                <li class="mb-4">
                  <a href="{{ \App\Support\LayoutLinks::hrefForKey($link['key'] ?? null) }}" class="text-white text-decoration-none small">
                    <span data-i18n="footer_main_{{ $mainMid + $ri }}">{{ $link['label_ar'] ?? '' }}</span>
                  </a>
                </li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>

      <div class="col col-lg-3 col-md-6 text-white" style="text-align: start">
        <h5 class="fw-bold mb-4 text-white" data-i18n="footer_quick_links">{{ $footerQuickTitleAr }}</h5>
        <ul class="list-unstyled p-0 footer-links">
          @foreach ($footerQuickLinks as $qi => $link)
            <li class="mb-4">
              <a href="{{ \App\Support\LayoutLinks::hrefForItem($link) }}" class="text-white text-decoration-none small" @if(filled($link['url'] ?? null) && ($link['url'] ?? '') !== '#' && str_starts_with((string) ($link['url'] ?? ''), 'http')) target="_blank" rel="noopener" @endif>
                <span data-i18n="footer_quick_{{ $qi }}">{{ $link['label_ar'] ?? '' }}</span>
              </a>
            </li>
          @endforeach
        </ul>
      </div>

      <div class="col-lg-3 col-md-6 text-white" style="text-align: start !important">
        <h5 class="fw-bold mb-4 text-white footer-stay-updated" data-i18n="footer_stay_updated">
          {{ $footerNewsletterTitleAr }}
        </h5>
        <p class="mb-3 text-white font-16 footer-subscribe" data-i18n="footer_subscribe">
          {{ $footerNewsletterDescAr }}
        </p>
        <div class="custom-newsletter-input mb-4">
          <div class="newsletter-input-wrapper">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input
              type="email"
              placeholder="ادخل بريدك الالكتروني"
              class="font-18 fw-bold"
              data-i18n-placeholder="footer_email_placeholder"
            />
          </div>
          <button class="rs-send" type="button">
            <i>
              <svg xmlns="http://www.w3.org/2000/svg" width="1.4em" height="1.2em" viewBox="0 0 24 24">
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

        <div class="contact-info-footer text-white">
          <p class="mb-2 d-flex align-items-center justify-content-start font-16">
            <i style="color: rgba(225, 114, 59, 1)">
              <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 24 24">
                <path d="M0 0h24v24H0z" fill="none" />
                <path
                  fill="currentColor"
                  d="M19.2 20q-2.702 0-5.418-1.244t-5.005-3.533q-2.27-2.289-3.523-5.021Q4 7.469 4 4.8V4h4.439l.848 4.083l-2.696 2.51q.684 1.186 1.417 2.167t1.527 1.769q.802.84 1.808 1.57t2.296 1.44l2.611-2.708l3.75.756V20zM6.121 9.654l2.092-1.92L7.635 5h-2.63q.03 1.144.309 2.305q.278 1.16.807 2.349m8.45 8.335q.923.463 2.09.723t2.339.277v-2.605l-2.388-.475zm0 0"
                />
              </svg>
            </i>
            {{ $contactPhone }}
          </p>
          <p class="mb-0 font-16 d-flex align-items-center justify-content-start">
            <i style="color: rgba(225, 114, 59, 1)">
              <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 24 24">
                <path d="M0 0h24v24H0z" fill="none" />
                <g fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="1.5">
                  <path stroke-linecap="round" d="m7 8.5l2.942 1.74c1.715 1.014 2.4 1.014 4.116 0L17 8.5" />
                  <path
                    d="M2.016 13.476c.065 3.065.098 4.598 1.229 5.733c1.131 1.136 2.705 1.175 5.854 1.254c1.94.05 3.862.05 5.802 0c3.149-.079 4.723-.118 5.854-1.254c1.131-1.135 1.164-2.668 1.23-5.733c.02-.986.02-1.966 0-2.952c-.066-3.065-.099-4.598-1.23-5.733c-1.131-1.136-2.705-1.175-5.854-1.254a115 115 0 0 0-5.802 0c-3.149.079-4.723.118-5.854 1.254c-1.131 1.135-1.164 2.668-1.23 5.733a69 69 0 0 0 0 2.952Z"
                  />
                </g>
              </svg>
            </i>
            {{ $contactEmail }}
          </p>
        </div>
      </div>
    </div>
  </div>

  <hr class="text-white opacity-25" style="width: 95%; margin: 20px auto" />

  <div style="width: 95%; margin: 0 auto">
    <div class="row align-items-center gy-4">
      <div class="col-12 col-md-6 order-md-2 text-center">
        <div class="d-flex gap-3 justify-content-md-end justify-content-center">
          <a href="{{ $instagramUrl }}" class="text-white footer-social-icon" target="_blank" rel="noopener">
            <i class="fab fa-instagram"></i>
          </a>
          <a href="{{ $twitterUrl }}" class="text-white footer-social-icon" target="_blank" rel="noopener">
            <i class="fab fa-twitter"></i>
          </a>
          <a href="{{ $telegramUrl }}" class="text-white footer-social-icon" target="_blank" rel="noopener">
            <i class="fab fa-telegram-plane"></i>
          </a>
          <a href="{{ $facebookUrl }}" class="text-white footer-social-icon" target="_blank" rel="noopener">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="{{ $linkedinUrl }}" class="text-white footer-social-icon" target="_blank" rel="noopener">
            <i class="fab fa-linkedin-in"></i>
          </a>
        </div>
      </div>

      <div class="col-12 col-md-6 order-md-1 text-center text-md-end">
        <p class="mb-0 small">
          <span data-i18n="footer_copyright">{{ $footerCopyrightAr }}</span>
          <span class="text-white" style="background-color: #e1723b" data-i18n="footer_rights_brand">{{ $footerBrand }}</span>
        </p>
      </div>
    </div>
  </div>
</footer>
