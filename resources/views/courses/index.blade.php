@extends('layouts.app', ['activeNav' => 'courses'])

@section('title', 'الكورسات — ' . \App\Models\Setting::get('site_name', 'Sawt'))

@section('content')
  <main class="container py-5" dir="rtl">
    <div class="text-center mb-5">
      <h1 class="fw-bold mb-2" style="color: #38422a">الكورسات</h1>
      <p class="text-muted mb-0">تصفّح الكورسات الحضورية وقدّم طلب انضمام بعد تسجيل الدخول</p>
    </div>

    @if ($courses->isEmpty())
      <div class="alert alert-light border text-center py-5">لا توجد كورسات منشورة حالياً.</div>
    @else
      <div class="row g-4">
        @foreach ($courses as $course)
          <div class="col-12 col-md-6 col-lg-4">
            <a href="{{ route('courses.show', $course) }}" class="text-decoration-none text-dark">
              <article class="h-100 border rounded-4 overflow-hidden shadow-sm bg-white">
                <div style="height: 200px; background: #edefebe6">
                  @if ($course->image)
                    <img
                      src="{{ str_starts_with($course->image, 'http') ? $course->image : asset('storage/'.$course->image) }}"
                      alt="{{ $course->title }}"
                      class="w-100 h-100"
                      style="object-fit: cover"
                    />
                  @endif
                </div>
                <div class="p-3">
                  <span class="badge mb-2" style="background: #38422a">
                    {{ ($course->delivery_mode ?? 'offline') === 'offline' ? 'حضوري' : 'إلكتروني' }}
                  </span>
                  <h2 class="h5 fw-bold mb-2">{{ $course->title }}</h2>
                  @if ($course->location)
                    <p class="small text-muted mb-1">
                      <i class="fa-solid fa-location-dot ms-1"></i>{{ $course->location }}
                    </p>
                  @endif
                  @if ($course->starts_at)
                    <p class="small text-muted mb-0">
                      <i class="fa-regular fa-calendar ms-1"></i>{{ $course->starts_at->format('Y-m-d') }}
                    </p>
                  @endif
                </div>
              </article>
            </a>
          </div>
        @endforeach
      </div>

      <div class="mt-4 d-flex justify-content-center">
        {{ $courses->links() }}
      </div>
    @endif
  </main>
@endsection
