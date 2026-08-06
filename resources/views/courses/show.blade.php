@extends('layouts.app', ['activeNav' => 'courses'])

@section('title', $course->title . ' — ' . \App\Models\Setting::get('site_name', 'Sawt'))

@section('content')
  <main class="container py-5" dir="rtl">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="rounded-4 overflow-hidden mb-3" style="background: #edefebe6; min-height: 260px">
          @if ($course->image)
            <img
              src="{{ str_starts_with($course->image, 'http') ? $course->image : asset('storage/'.$course->image) }}"
              alt="{{ $course->title }}"
              class="w-100"
              style="max-height: 360px; object-fit: cover"
            />
          @endif
        </div>

        <h1 class="fw-bold mb-3" style="color: #38422a">{{ $course->title }}</h1>
        <p class="lh-lg text-secondary">{{ $course->description }}</p>

        <ul class="list-unstyled mt-4">
          <li class="mb-2">
            <strong>النوع:</strong>
            {{ ($course->delivery_mode ?? 'offline') === 'offline' ? 'حضوري (أوفلاين)' : 'إلكتروني' }}
          </li>
          @if ($course->location)
            <li class="mb-2"><strong>المكان:</strong> {{ $course->location }}
              @if ($course->location_details) — {{ $course->location_details }} @endif
            </li>
          @endif
          @if ($course->starts_at)
            <li class="mb-2"><strong>يبدأ:</strong> {{ $course->starts_at->format('Y-m-d H:i') }}</li>
          @endif
          @if ($course->ends_at)
            <li class="mb-2"><strong>ينتهي:</strong> {{ $course->ends_at->format('Y-m-d H:i') }}</li>
          @endif
          @if ($course->max_seats)
            <li class="mb-2"><strong>المقاعد:</strong> {{ $course->max_seats }}</li>
          @endif
          @if ($course->instructor)
            <li class="mb-2"><strong>المدرّب:</strong> {{ $course->instructor->username }}</li>
          @endif
        </ul>

        @if (is_array($course->requirements) && count($course->requirements))
          <div class="mt-3">
            <h2 class="h5 fw-bold">المتطلبات</h2>
            <ul>
              @foreach ($course->requirements as $req)
                <li>{{ $req }}</li>
              @endforeach
            </ul>
          </div>
        @endif
      </div>

      <div class="col-lg-5">
        <div class="border rounded-4 p-4 shadow-sm bg-white sticky-top" style="top: 24px">
          <h2 class="h5 fw-bold mb-3">طلب الانضمام</h2>

          @auth
            @php
              $status = $joinRequest?->status;
              $joined = $course->isJoinedBy(auth()->user());
            @endphp

            @if ($joined || $status === 'accepted')
              <div class="alert alert-success mb-0">تم قبول انضمامك إلى هذا الكورس.</div>
            @elseif ($status === 'pending')
              <div class="alert alert-warning mb-0">طلبك قيد المراجعة من الإدارة.</div>
            @elseif (! $course->hasAvailableSeats())
              <div class="alert alert-secondary mb-0">اكتملت المقاعد المتاحة لهذا الكورس.</div>
            @else
              @if ($status === 'rejected')
                <div class="alert alert-danger">تم رفض طلبك السابق. يمكنك إعادة التقديم.</div>
              @endif

              <form method="POST" action="{{ route('courses.join', $course) }}">
                @csrf
                <div class="mb-3">
                  <label class="form-label">الاسم الكامل</label>
                  <input
                    type="text"
                    name="full_name"
                    class="form-control"
                    value="{{ old('full_name', $joinRequest->full_name ?? auth()->user()->name) }}"
                    required
                  />
                </div>
                <div class="mb-3">
                  <label class="form-label">البريد الإلكتروني</label>
                  <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="{{ old('email', $joinRequest->email ?? auth()->user()->email) }}"
                  />
                </div>
                <div class="mb-3">
                  <label class="form-label">رقم الهاتف</label>
                  <input
                    type="text"
                    name="phone"
                    class="form-control"
                    value="{{ old('phone', $joinRequest->phone ?? auth()->user()->phone) }}"
                  />
                </div>
                <div class="mb-3">
                  <label class="form-label">لماذا تريد الانضمام؟</label>
                  <textarea name="message" class="form-control" rows="4" required>{{ old('message', $joinRequest->message ?? '') }}</textarea>
                </div>
                <button type="submit" class="btn w-100 text-white" style="background: #38422a">
                  إرسال طلب الانضمام
                </button>
              </form>
            @endif
          @else
            <p class="text-muted">يجب تسجيل الدخول أو إنشاء حساب قبل إرسال طلب الانضمام.</p>
            <div class="d-grid gap-2">
              <a href="{{ route('login') }}" class="btn text-white" style="background: #38422a">تسجيل الدخول</a>
              <a href="{{ route('register') }}" class="btn btn-outline-secondary">إنشاء حساب</a>
            </div>
          @endauth
        </div>
      </div>
    </div>

    <div class="mt-4">
      <a href="{{ route('courses.index') }}" class="text-decoration-none" style="color: #e1723b">← العودة للكورسات</a>
    </div>
  </main>
@endsection
