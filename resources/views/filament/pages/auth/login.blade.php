{{-- Filament admin login — adds lockout countdown above the stock form --}}
<x-filament-panels::page.simple>
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    {{-- Visible countdown while login is temporarily locked --}}
    @if (($this->secondsUntilRetry ?? 0) > 0)
        <div
            wire:poll.1s="tickLockoutTimer"
            class="mb-6 rounded-xl border border-danger-300 bg-danger-50 px-4 py-3 text-center text-sm text-danger-700 dark:border-danger-500/40 dark:bg-danger-500/10 dark:text-danger-300"
            role="alert"
            aria-live="polite"
        >
            <div class="font-semibold">
                {{ __('filament-panels::pages/auth/login.notifications.throttled.title') }}
            </div>
            <div class="mt-1">
                {{ __('admin.login.lockout_timer', ['time' => $this->lockoutTimerLabel]) }}
            </div>
            <div
                class="mt-3 font-mono text-3xl font-bold tracking-wider text-danger-600 dark:text-danger-400"
                wire:key="lockout-timer-{{ $this->secondsUntilRetry }}"
            >
                {{ $this->lockoutTimerLabel }}
            </div>
        </div>
    @endif

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
