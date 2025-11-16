<x-filament-widgets::widget>
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        wire:key="survey-{{ $survey?->id }}"
    >
        @if ($survey)
            <x-filament::section>
                <div
                    class="rounded-xl border border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-900/20 p-6 shadow-sm ring-1 ring-primary-950/5 dark:ring-primary-500/10"
                    x-data="{ transitioning: false }"
                >
                    <div class="flex flex-col gap-4">
                        <div class="flex items-start gap-4">
                            <x-filament::icon
                                icon="heroicon-o-clipboard-document-list"
                                class="h-10 w-10 text-primary-600 flex-shrink-0 mt-1"
                            />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-4 mb-2">
                                    <h3 class="text-xl font-semibold leading-6 text-primary-900 dark:text-primary-100">
                                        {{ $survey->title }}
                                    </h3>
                                    <button
                                        wire:click="dismiss"
                                        @click="transitioning = true"
                                        type="button"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors flex-shrink-0"
                                        title="Dismiss"
                                    >
                                        <x-filament::icon
                                            icon="heroicon-m-x-mark"
                                            class="h-5 w-5"
                                        />
                                    </button>
                                </div>
                                @if ($survey->description)
                                    <p class="text-sm text-primary-700 dark:text-primary-300 leading-relaxed">
                                        {{ $survey->description }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-2">
                            <button
                                wire:click="takeSurvey"
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-colors duration-200 flex-1 sm:flex-initial"
                            >
                                Take Survey
                                <x-filament::icon
                                    icon="heroicon-m-arrow-right"
                                    class="h-4 w-4"
                                />
                            </button>

                            <button
                                wire:click="notInterested"
                                @click="transitioning = true"
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
                            >
                                Not Interested
                            </button>

                            <button
                                wire:click="remindLater"
                                @click="transitioning = true"
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
                            >
                                Maybe Later
                            </button>
                        </div>

                        <div class="flex items-center justify-center pt-2 border-t border-primary-100 dark:border-primary-900/50 mt-2">
                            <a
                                href="{{ route('filament.admin.pages.my-surveys') }}"
                                class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300 transition-colors duration-200"
                            >
                                View All Surveys
                                <x-filament::icon
                                    icon="heroicon-m-arrow-right"
                                    class="h-4 w-4"
                                />
                            </a>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-widgets::widget>
