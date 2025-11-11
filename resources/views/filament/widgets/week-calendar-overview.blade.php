<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold tracking-tight">
                Week at a Glance
            </h2>
            <a href="{{ route('filament.admin.pages.booking-calendar') }}" class="text-primary-600 hover:text-primary-500 transition-colors duration-200">
                View Full Calendar →
            </a>
        </div>

        <div class="grid grid-cols-7 gap-1">
            @foreach ($weekDays as $day)
                <div class="relative group">
                    <div @class([
                        'rounded-lg border bg-gray-800/50 dark:bg-gray-800/50 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden',
                        'border-primary-500 dark:border-primary-400 border-2' => $day['isToday'],
                        'border-gray-700 dark:border-gray-700' => ! $day['isToday'],
                    ])>
                        {{-- Day Header --}}
                        <div @class([
                            'p-2 border-b',
                            'bg-primary-900/40 dark:bg-primary-900/40 border-primary-700 dark:border-primary-700' => $day['isToday'],
                            'bg-gray-900/50 dark:bg-gray-900/50 border-gray-700 dark:border-gray-700' => ! $day['isToday'],
                        ])>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-medium text-gray-400 dark:text-gray-400 uppercase tracking-wide">
                                        {{ $day['dayName'] }}
                                    </div>
                                    <div @class([
                                        'text-base font-bold mt-0.5',
                                        'text-primary-400 dark:text-primary-400' => $day['isToday'],
                                        'text-gray-100 dark:text-gray-100' => ! $day['isToday'],
                                    ])>
                                        {{ $day['monthName'] }} {{ $day['dayNumber'] }}
                                    </div>
                                </div>
                                @if ($day['isToday'])
                                    <div class="flex items-center gap-1">
                                        <div class="h-2 w-2 rounded-full bg-primary-500 animate-pulse"></div>
                                        <span class="text-xs font-medium text-primary-600 dark:text-primary-400">Today</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Bookings --}}
                        <div class="p-2 space-y-2 min-h-[200px]">
                            @forelse ($day['bookings'] as $booking)
                                <div
                                    class="rounded-lg p-2 border-l-4 transition-all duration-200 hover:shadow-md"
                                    style="border-left-color: {{ $booking['color'] }}; background-color: {{ $booking['color'] }}15;"
                                >
                                    {{-- Time Range --}}
                                    <div class="flex items-center gap-1 text-xs font-semibold text-gray-900 dark:text-gray-100">
                                        <x-filament::icon
                                            icon="heroicon-m-clock"
                                            class="h-3 w-3"
                                        />
                                        <span>{{ $booking['start_time'] }} - {{ $booking['end_time'] }}</span>
                                    </div>

                                    {{-- Title --}}
                                    @if ($booking['title'])
                                        <div class="mt-1 text-xs font-medium text-gray-800 dark:text-gray-200 line-clamp-1">
                                            {{ $booking['title'] }}
                                        </div>
                                    @endif

                                    {{-- Areas --}}
                                    @if ($booking['areas'])
                                        <div class="mt-1 flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                            <x-filament::icon
                                                icon="heroicon-m-map-pin"
                                                class="h-3 w-3"
                                            />
                                            <span class="line-clamp-1">{{ $booking['areas'] }}</span>
                                        </div>
                                    @endif

                                    {{-- User Name --}}
                                    @if ($booking['user_name'])
                                        <div class="mt-1 flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                            <x-filament::icon
                                                icon="heroicon-m-user"
                                                class="h-3 w-3"
                                            />
                                            <span class="line-clamp-1">{{ $booking['user_name'] }}</span>
                                        </div>
                                    @endif

                                    {{-- Event Type Badge --}}
                                    <div class="mt-1">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                            style="background-color: {{ $booking['color'] }}; color: white;"
                                        >
                                            {{ $booking['event_type']->getLabel() }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center h-[180px] text-center">
                                    <x-filament::icon
                                        icon="heroicon-o-calendar"
                                        class="h-8 w-8 text-gray-400 dark:text-gray-600 mb-2"
                                    />
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        No bookings
                                    </p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Hover Effect --}}
                        <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-primary-500/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
