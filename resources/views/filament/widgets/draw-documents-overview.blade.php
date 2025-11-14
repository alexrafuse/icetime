
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold tracking-tight mb-2">
                Draw Schedules
            </h2>
            <a href="{{ route('filament.admin.resources.draw-documents.index') }}" class="text-primary-600 hover:text-primary-500">
                Manage Draws →
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($days as $dayNumber => $dayName)
                <div class="relative group">
                    <div @class([
                        'rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10',
                        'opacity-75' => ! isset($currentDraws[$dayNumber]),
                    ])>
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold leading-6">
                                {{ $dayName }}
                            </h3>

                            @if (isset($currentDraws[$dayNumber]))
                                <div class="flex items-center gap-1">
                                    <div class="h-2 w-2 rounded-full bg-success-500"></div>
                                    <span class="text-xs text-success-500">Active</span>
                                </div>
                            @else
                                <div class="flex items-center gap-1">
                                    <div class="h-2 w-2 rounded-full bg-gray-400"></div>
                                    <span class="text-xs text-gray-500">No draw</span>
                                </div>
                            @endif
                        </div>

                        @if (isset($currentDraws[$dayNumber]))
                            @foreach ($currentDraws[$dayNumber] as $draw)
                                <div class="mt-3">
                                    <a
                                        href="{{ $draw->getFileUrl() }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-md text-primary-600 hover:text-primary-500 transition-colors duration-200"
                                    >
                                        <x-filament::icon
                                            icon="heroicon-m-document-text"
                                            class="h-6 w-6"
                                        />
                                        <span>{{ $draw->title }}</span>
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <p class="mt-3 text-sm text-gray-500">
                                No active draw document
                            </p>
                        @endif

                        <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-primary-500/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
