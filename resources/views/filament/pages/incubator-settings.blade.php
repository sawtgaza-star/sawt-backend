<x-filament-panels::page>
    {{-- IncubatorSettings Livewire form (header / footer / landing tabs) --}}
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit">
                {{ __('حفظ إعدادات الحاضنة') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
