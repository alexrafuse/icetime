@props([
    'icon' => 'heroicon-o-inbox',
    'heading',
    'description' => null,
    'action' => null,
    'actionLabel' => null,
    'actionIcon' => null,
])

<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center shadow-xs">
    <x-filament::icon
        :icon="$icon"
        class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4"
    />
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
        {{ $heading }}
    </h3>
    @if ($description)
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ $description }}
        </p>
    @endif
    @if ($action)
        <div class="mt-6">
            <a
                href="{{ $action }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-primary-500 transition-colors duration-200"
            >
                {{ $actionLabel }}
                @if ($actionIcon)
                    <x-filament::icon :icon="$actionIcon" class="h-4 w-4" />
                @endif
            </a>
        </div>
    @endif
</div>
