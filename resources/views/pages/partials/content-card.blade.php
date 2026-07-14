@php
  $img = $card['img'] ?? '/assets/images/1.png';
  $duration = $card['duration'] ?? '0:00';
  $title = $card['title'] ?? '';
  $subtitle = $card['subtitle'] ?? '';
  $tag = $card['tag'] ?? 'orange';
@endphp

<article class="content-media-card">
  <a href="#" class="content-media-link">
    <div class="content-media-thumb-wrap">
      <img src="{{ $img }}" alt="{{ $title }}" class="content-media-thumb" loading="lazy" />
      <span class="content-media-duration">{{ $duration }}</span>
      <img
        src="/assets/images/صوت ابيض.png"
        alt="صوت"
        class="content-media-logo"
      />
      <div class="content-media-play" aria-hidden="true">
        <i class="fa-solid fa-play"></i>
      </div>
      <div class="content-media-meta">
        @if ($subtitle !== '')
          <span class="content-media-banner content-media-banner--{{ $tag }}">{{ $subtitle }}</span>
        @endif
        @if ($title !== '')
          <span class="content-media-title">{{ $title }}</span>
        @endif
      </div>
    </div>
  </a>
</article>
