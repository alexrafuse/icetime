<x-filament-panels::page.simple>
    <x-filament-panels::form wire:submit="setPassword">
        {{ $this->form }}

        <x-filament::button
        type="submit"
        color="blue"
        >
            Set Password
        </x-filament::button>
    </x-filament-panels::form>
</x-filament-panels::page.simple>
