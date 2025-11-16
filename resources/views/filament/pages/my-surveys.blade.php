<x-filament-panels::page>
    @php
        $availableSurveys = $this->getAvailableSurveys();
        $pastSurveys = $this->getPastSurveys();
    @endphp

    <div class="space-y-8">
        {{-- Available Surveys Section --}}
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white mb-6">
                Available Surveys
            </h2>

            @if ($availableSurveys->isEmpty())
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center shadow-sm">
                    <x-filament::icon
                        icon="heroicon-o-clipboard-document-check"
                        class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4"
                    />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        All caught up!
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        You've completed all available surveys. Check back later for new ones.
                    </p>
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($availableSurveys as $survey)
                        <div class="rounded-xl border border-primary-200 dark:border-primary-800 bg-gradient-to-br from-primary-50 to-white dark:from-primary-900/20 dark:to-gray-800 p-6 shadow-md hover:shadow-lg transition-shadow duration-200 flex flex-col">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <x-filament::icon
                                        icon="heroicon-o-clipboard-document-list"
                                        class="h-8 w-8 text-primary-600 dark:text-primary-400 flex-shrink-0"
                                    />
                                    <span class="inline-flex items-center rounded-md bg-primary-100 dark:bg-primary-900/50 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-300">
                                        Priority {{ $survey->priority }}
                                    </span>
                                </div>
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 leading-snug">
                                {{ $survey->title }}
                            </h3>

                            @if ($survey->description)
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-6 leading-relaxed flex-1">
                                    {{ Str::limit($survey->description, 150) }}
                                </p>
                            @endif

                            <div class="flex flex-col gap-2 mt-auto">
                                <button
                                    wire:click="takeSurvey({{ $survey->id }})"
                                    type="button"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-colors duration-200"
                                >
                                    Take Survey
                                    <x-filament::icon
                                        icon="heroicon-m-arrow-right"
                                        class="h-4 w-4"
                                    />
                                </button>

                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        wire:click="notInterested({{ $survey->id }})"
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
                                    >
                                        Pass
                                    </button>

                                    <button
                                        wire:click="remindLater({{ $survey->id }})"
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
                                    >
                                        Later
                                    </button>

                                    <button
                                        wire:click="dismiss({{ $survey->id }})"
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
                                        title="Dismiss"
                                    >
                                        <x-filament::icon
                                            icon="heroicon-m-x-mark"
                                            class="h-4 w-4"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Past Surveys Section --}}
        @if ($pastSurveys->isNotEmpty())
            <div x-data="{ open: false }" class="border-t border-gray-200 dark:border-gray-700 pt-8">
                <button
                    @click="open = !open"
                    type="button"
                    class="w-full flex items-center justify-between text-left group mb-4"
                >
                    <h2 class="text-xl font-bold text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                        Past Surveys ({{ $pastSurveys->count() }})
                    </h2>
                    <x-filament::icon
                        icon="heroicon-m-chevron-down"
                        class="h-5 w-5 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                        ::class="{ 'rotate-180': open }"
                    />
                </button>

                <div
                    x-show="open"
                    x-collapse
                    class="space-y-3"
                >
                    @foreach ($pastSurveys as $item)
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-5 opacity-75 hover:opacity-100 transition-opacity duration-200">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 truncate">
                                            {{ $item['survey']->title }}
                                        </h3>
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                            {{ $item['status']->value === 'completed' ? 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300' : '' }}
                                            {{ $item['status']->value === 'not_interested' ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' : '' }}
                                            {{ $item['status']->value === 'dismissed' ? 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300' : '' }}
                                            {{ $item['status']->value === 'later' ? 'bg-info-100 dark:bg-info-900/30 text-info-700 dark:text-info-300' : '' }}
                                        ">
                                            {{ $item['status']->getLabel() }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $item['responded_at']->diffForHumans() }}
                                    </p>
                                </div>

                                @if ($item['status']->value !== 'completed' && $item['status']->value !== 'not_interested')
                                    <button
                                        wire:click="takeSurvey({{ $item['survey']->id }})"
                                        type="button"
                                        class="flex-shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-primary-500 transition-colors duration-200"
                                    >
                                        Take Now
                                        <x-filament::icon
                                            icon="heroicon-m-arrow-right"
                                            class="h-3 w-3"
                                        />
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
