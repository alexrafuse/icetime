<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header Section --}}
        <div class="flex flex-col gap-4 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                        Week at a Glance
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $this->weekDateRange }}
                    </p>
                </div>
                <a
                    href="{{ route('filament.admin.pages.booking-calendar') }}"
                    class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200 flex items-center gap-1.5"
                >
                    <span>View Full Calendar</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            {{-- Week Navigation --}}
            <div class="flex items-center justify-center gap-2">
                <button
                    wire:click="previousWeek"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-600 transition-colors data-loading:opacity-50 data-loading:cursor-wait"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span>Previous Week</span>
                </button>

                <button
                    wire:click="goToCurrentWeek"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 dark:bg-primary-500 rounded-lg hover:bg-primary-700 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-600 transition-colors data-loading:opacity-50 data-loading:cursor-wait"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Today</span>
                </button>

                <button
                    wire:click="nextWeek"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-600 transition-colors data-loading:opacity-50 data-loading:cursor-wait"
                >
                    <span>Next Week</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Calendar Grid --}}
        <div class="grid grid-cols-7 gap-4 data-loading:opacity-60 transition-opacity">
            @foreach ($this->weekDays as $day)
                <div class="relative group">
                    <div @class([
                        'rounded-xl bg-white dark:bg-gray-900/50 overflow-hidden transition-all duration-300',
                        'ring-2 ring-primary-500 dark:ring-primary-500 shadow-lg shadow-primary-500/20' => $day['isToday'],
                        'ring-1 ring-gray-200 dark:ring-gray-700 shadow-sm hover:shadow-md hover:ring-gray-300 dark:hover:ring-gray-600' => ! $day['isToday'],
                    ])>
                        {{-- Day Header --}}
                        <div @class([
                            'px-3 py-2 border-b',
                            'bg-gray-50 dark:bg-gray-800/80 border-primary-500/50 dark:border-primary-500/50' => $day['isToday'],
                            'bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700' => ! $day['isToday'],
                        ])>
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div @class([
                                        'text-xs font-semibold uppercase tracking-wider',
                                        'text-primary-600 dark:text-primary-400' => $day['isToday'],
                                        'text-gray-500 dark:text-gray-400' => ! $day['isToday'],
                                    ])>
                                        {{ $day['dayName'] }}
                                    </div>
                                    <div @class([
                                        'text-2xl font-bold mt-1 leading-none',
                                        'text-primary-600 dark:text-primary-400' => $day['isToday'],
                                        'text-gray-900 dark:text-gray-100' => ! $day['isToday'],
                                    ])>
                                        {{ $day['dayNumber'] }}
                                    </div>
                                    <div @class([
                                        'text-xs font-medium mt-0.5',
                                        'text-primary-600 dark:text-primary-400' => $day['isToday'],
                                        'text-gray-500 dark:text-gray-400' => ! $day['isToday'],
                                    ])>
                                        {{ $day['monthName'] }}
                                    </div>
                                </div>
                                @if ($day['isToday'])
                                    <div class="flex items-center gap-1.5 ml-2">
                                        <span class="relative flex h-2.5 w-2.5">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary-500"></span>
                                        </span>
                                        <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">Today</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Bookings --}}
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($day['bookings'] as $booking)
                                <div
                                    class="py-3 px-2 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-150 border-l-2"
                                    style="border-left-color: {{ $booking['color'] }};"
                                >
                                    {{-- Title --}}
                                    @if ($booking['title'])
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white mb-2 leading-tight">
                                            {{ $booking['title'] }}
                                        </div>
                                    @endif

                                    {{-- Time Range --}}
                                    <div class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                                        {{ $booking['start_time'] }} - {{ $booking['end_time'] }}
                                    </div>

                                    {{-- Event Type Badge and Info Button --}}
                                    <div class="flex items-center justify-between">
                                        {{-- Info Button --}}
                                        @if ($booking['areas'] || $booking['user_name'])
                                            <div class="relative group">
                                                <button type="button" class="flex items-center justify-center h-6 w-6 rounded-full text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>

                                                {{-- Tooltip --}}
                                                <div class="absolute left-0 bottom-full mb-2 z-50 hidden group-hover:block">
                                                    <div class="bg-white dark:bg-gray-950 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-3 min-w-[200px]">
                                                        @if ($booking['areas'])
                                                            <div class="flex items-start gap-2 mb-2">
                                                                <x-filament::icon
                                                                    icon="heroicon-m-map-pin"
                                                                    class="h-4 w-4 text-gray-500 dark:text-gray-400 mt-0.5 flex-shrink-0"
                                                                />
                                                                <div>
                                                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">Location</div>
                                                                    <div class="text-xs text-gray-900 dark:text-gray-200">{{ $booking['areas'] }}</div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if ($booking['user_name'])
                                                            <div class="flex items-start gap-2">
                                                                <x-filament::icon
                                                                    icon="heroicon-m-user"
                                                                    class="h-4 w-4 text-gray-500 dark:text-gray-400 mt-0.5 flex-shrink-0"
                                                                />
                                                                <div>
                                                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">Contact</div>
                                                                    <div class="text-xs text-gray-900 dark:text-gray-200">{{ $booking['user_name'] }}</div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    {{-- Arrow --}}
                                                    <div class="absolute left-3 -bottom-1 w-2 h-2 bg-white dark:bg-gray-950 border-r border-b border-gray-200 dark:border-gray-700 rotate-45"></div>
                                                </div>
                                            </div>
                                        @else
                                            {{-- Spacer when no info button --}}
                                            <div></div>
                                        @endif

                                        {{-- Event Type Badge --}}
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                            style="background-color: {{ $booking['color'] }}20; color: {{ $booking['color'] }};"
                                        >
                                            {{ $booking['event_type']->getLabel() }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-8 text-center">
                                    <x-filament::icon
                                        icon="heroicon-o-calendar"
                                        class="h-8 w-8 text-gray-300 dark:text-gray-600 mb-2"
                                    />
                                    <p class="text-sm text-gray-400 dark:text-gray-500">
                                        No bookings
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
