<div class="fi-topbar-item">
    <div class="flex items-center gap-1 rounded-lg bg-gray-50 p-1 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
        <a
            href="{{ route('lang.switch', 'ar') }}"
            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-sm font-semibold transition
                {{ app()->getLocale() === 'ar'
                    ? 'bg-white text-primary-600 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/10 dark:text-primary-400 dark:ring-white/10'
                    : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
            title="العربية"
        >
            <x-heroicon-o-language class="h-4 w-4" />
            <span>AR</span>
        </a>

        <a
            href="{{ route('lang.switch', 'en') }}"
            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-sm font-semibold transition
                {{ app()->getLocale() === 'en'
                    ? 'bg-white text-primary-600 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/10 dark:text-primary-400 dark:ring-white/10'
                    : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
            title="English"
        >
            <x-heroicon-o-language class="h-4 w-4" />
            <span>EN</span>
        </a>
    </div>
</div>
