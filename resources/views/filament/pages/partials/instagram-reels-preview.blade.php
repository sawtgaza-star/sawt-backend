@if (\App\Models\Setting::get('reels_enabled', false))
    @php($reels = $livewire->getReels())

    <x-filament::section class="mt-4">
        <x-slot name="heading">
            <div class="flex items-center justify-between gap-4">
                <span>{{ __('ريلز إنستغرام الحية') }}</span>
                <x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-path" wire:click="refreshReels">
                    {{ __('تحديث الآن') }}
                </x-filament::button>
            </div>
        </x-slot>

        @if (empty($reels))
            <div class="text-sm text-gray-500 dark:text-gray-400 py-6 text-center">
                @php($ig = app(\App\Services\InstagramService::class))
                @if ($ig->isTokenPastLocalExpiry())
                    {{ __('انتهت صلاحية توكن إنستغرام (شهرين). جدّد رمز الوصول بالأعلى ثم احفظ.') }}
                @else
                    {{ __('لا يوجد ريلز للعرض. تأكد من إدخال معرّف الحساب ورمز الوصول بالأعلى ثم اضغط حفظ.') }}
                @endif
            </div>
        @else
            <div
                x-data="{ open: false, src: '', video: '', embed: '', link: '' }"
                @keydown.escape.window="open = false; src = ''"
            >
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($reels as $reel)
                        <button
                            type="button"
                            @click="src = ''; video = @js($reel['video_url']); embed = @js(rtrim((string) $reel['permalink'], '/').'/embed/captioned/'); link = @js($reel['permalink']); open = true"
                            class="group block overflow-hidden rounded-xl text-start ring-1 ring-gray-200 dark:ring-white/10 transition hover:ring-violet-500 hover:shadow-lg"
                        >
                            <div class="relative aspect-[9/16] bg-gray-100 dark:bg-gray-800">
                                @if ($reel['thumbnail'])
                                    <img
                                        src="{{ $reel['thumbnail'] }}"
                                        alt="reel"
                                        loading="lazy"
                                        class="h-full w-full object-cover transition group-hover:scale-105"
                                    >
                                @endif
                                <div class="absolute inset-x-0 top-0 flex items-center gap-1.5 bg-gradient-to-b from-black/60 to-transparent p-2 text-xs font-semibold text-white">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-md" style="background: linear-gradient(45deg,#f58529,#dd2a7b,#8134af,#515bd4);">
                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="white" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="white"/></svg>
                                    </span>
                                    {{ '@'.($reel['username'] ?? 'instagram') }}
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center opacity-80 transition group-hover:opacity-100">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-black/45 backdrop-blur">
                                        <x-heroicon-s-play class="h-6 w-6 text-white" />
                                    </span>
                                </div>
                                <div class="absolute inset-x-0 bottom-0 flex items-center gap-3 bg-gradient-to-t from-black/70 to-transparent p-2 text-xs text-white">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-s-heart class="h-3.5 w-3.5" /> {{ $reel['likes'] }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-s-chat-bubble-oval-left class="h-3.5 w-3.5" /> {{ $reel['comments'] }}
                                    </span>
                                </div>
                            </div>
                            @if (! empty($reel['caption']))
                                <p class="line-clamp-2 p-2 text-xs text-gray-600 dark:text-gray-300">
                                    {{ \Illuminate\Support\Str::limit($reel['caption'], 80) }}
                                </p>
                            @endif
                            @if (! empty($reel['comment_items']))
                                <ul class="space-y-1 border-t border-gray-100 p-2 text-xs dark:border-white/10">
                                    @foreach (array_slice($reel['comment_items'], 0, 3) as $comment)
                                        <li class="line-clamp-2 text-gray-600 dark:text-gray-300">
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ '@'.$comment['name'] }}</span>
                                            {{ $comment['text'] }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </button>
                    @endforeach
                </div>

                <template x-teleport="body">
                    <div
                        x-show="open"
                        x-transition.opacity
                        style="display: none; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);"
                        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60 p-4"
                        @click.self="open = false; src = ''"
                    >
                        <div class="relative w-full max-w-sm" x-trap.noscroll="open">
                            <button
                                type="button"
                                @click="open = false; src = ''"
                                class="absolute -top-10 end-0 flex items-center gap-1 text-sm text-white/90 hover:text-white"
                            >
                                {{ __('إغلاق') }} <x-heroicon-o-x-mark class="h-5 w-5" />
                            </button>

                            <video
                                x-show="src"
                                :src="src"
                                controls
                                autoplay
                                playsinline
                                loop
                                class="aspect-[9/16] w-full rounded-2xl bg-black shadow-2xl"
                            ></video>

                            {{-- Instagram's own player (account, caption, likes) — the default view --}}
                            <iframe
                                x-show="!src && embed"
                                :src="open && !src ? embed : ''"
                                allowfullscreen
                                scrolling="no"
                                class="w-full rounded-2xl bg-white shadow-2xl"
                                style="height: min(80vh, 720px);"
                            ></iframe>

                            <div class="mt-3 flex items-center justify-center gap-4 text-sm text-white/80">
                                <button
                                    type="button"
                                    x-show="video"
                                    @click="src = src ? '' : video"
                                    class="underline hover:text-white"
                                    x-text="src ? @js(__('عرض مشغّل إنستغرام')) : @js(__('تشغيل الفيديو مباشرة'))"
                                ></button>
                                <a :href="link" target="_blank" rel="noopener" class="underline hover:text-white">
                                    {{ __('افتح في إنستغرام') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        @endif
    </x-filament::section>
@endif
