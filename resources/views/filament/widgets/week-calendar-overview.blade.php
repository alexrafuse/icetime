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
        <div class="grid grid-cols-7 gap-2 data-loading:opacity-60 transition-opacity">
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
                        </div>

                        {{-- Bookings --}}
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($day['bookings'] as $booking)
                                <div class="py-3 px-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-150">
                                    {{-- Title --}}
                                    @if ($booking['title'])
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white mb-2 leading-tight">
                                            {{ $booking['title'] }}
                                        </div>
                                    @endif

                                    {{-- Time Range --}}
                                    <div class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                        {{ $booking['start_time'] }} - {{ $booking['end_time'] }}
                                    </div>

                                    {{-- Event Type Dot --}}
                                    <div class="flex justify-end">
                                        <span class="inline-block w-2 h-2 rounded-full" style="background-color: {{ $booking['color'] }};"></span>
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

        {{-- Legend --}}
        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-center gap-6 flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full" style="background-color: #4ade80;"></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Private</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full" style="background-color: #3b82f6;"></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">League</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full" style="background-color: #f97316;"></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tournament</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full" style="background-color: #a855f7;"></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Drop-in</span>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
