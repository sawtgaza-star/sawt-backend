<x-filament-panels::page>
    {{-- MediaSettings Livewire form (header / footer / landing tabs) --}}
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit">
                {{ __('حفظ إعدادات ميديا') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
