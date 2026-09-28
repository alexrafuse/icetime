<x-filament-panels::page.simple>
    <form wire:submit="setPassword" class="grid gap-y-6">
        {{ $this->form }}

        <x-filament::button
        type="submit"
        color="blue"
        >
            Set Password
        </x-filament::button>
    </form>
</x-filament-panels::page.simple>
