<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit">
                حفظ الإعدادات
            </x-filament::button>
        </div>
    </form>

    {{-- ===== معاينة الريلز الحية من إنستغرام ===== --}}
    @if (\App\Models\Setting::get('reels_enabled', false))
        @php($reels = $this->getReels())

        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center justify-between gap-4">
                    <span>ريلز إنستغرام الحية</span>
                    <x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-path" wire:click="refreshReels">
                        تحديث الآن
                    </x-filament::button>
                </div>
            </x-slot>

            @if (empty($reels))
                <div class="text-sm text-gray-500 dark:text-gray-400 py-6 text-center">
                    لا يوجد ريلز للعرض. تأكد من إدخال <strong>معرّف الحساب</strong> و<strong>رمز الوصول</strong> بالأعلى ثم اضغط حفظ.
                </div>
            @else
                {{-- مشغّل داخل الصفحة: الضغط على أي ريل يفتحه في نافذة منبثقة --}}
                <div
                    x-data="{ open: false, src: '', embed: '', link: '' }"
                    @keydown.escape.window="open = false; src = ''; embed = ''"
                >
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach ($reels as $reel)
                            <button
                                type="button"
                                @click="src = @js($reel['video_url']); embed = @js(rtrim((string) $reel['permalink'], '/').'/embed'); link = @js($reel['permalink']); open = true"
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
                                    {{-- أيقونة تشغيل --}}
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
                            </button>
                        @endforeach
                    </div>

                    {{-- النافذة المنبثقة (المشغّل) --}}
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
                                    @click="open = false; src = ''; embed = ''"
                                    class="absolute -top-10 end-0 flex items-center gap-1 text-sm text-white/90 hover:text-white"
                                >
                                    إغلاق <x-heroicon-o-x-mark class="h-5 w-5" />
                                </button>

                                {{-- المشغّل المباشر (فيديو إنستغرام) --}}
                                <video
                                    x-show="src"
                                    :src="src"
                                    controls
                                    autoplay
                                    playsinline
                                    loop
                                    class="aspect-[9/16] w-full rounded-2xl bg-black shadow-2xl"
                                ></video>

                                {{-- احتياطي: تضمين إنستغرام عند عدم توفر رابط الفيديو المباشر --}}
                                <iframe
                                    x-show="!src && embed"
                                    :src="embed"
                                    allowfullscreen
                                    scrolling="no"
                                    class="aspect-[9/16] w-full rounded-2xl bg-black shadow-2xl"
                                ></iframe>

                                <a
                                    :href="link"
                                    target="_blank"
                                    rel="noopener"
                                    class="mt-3 block text-center text-sm text-white/80 underline hover:text-white"
                                >
                                    افتح في إنستغرام
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
