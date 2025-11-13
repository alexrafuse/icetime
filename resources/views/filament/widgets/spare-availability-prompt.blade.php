<x-filament-widgets::widget>
    <x-filament::section>
        @if ($hasSpareAvailability)
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <x-filament::icon
                            icon="heroicon-o-user-group"
                            class="h-8 w-8 text-primary-600 flex-shrink-0"
                        />
                        <h3 class="text-xl font-semibold leading-6">
                            Need a spare tonight?
                        </h3>
                    </div>
                    <div class="flex-shrink-0">
                        <a
                            href="{{ route('filament.admin.resources.spare-availabilities.index') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-colors duration-200"
                        >
                            View Spare List
                            <x-filament::icon
                                icon="heroicon-m-arrow-right"
                                class="h-4 w-4"
                            />
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-warning-200 dark:border-warning-800 bg-warning-50 dark:bg-warning-900/20 p-6 shadow-sm ring-1 ring-warning-950/5 dark:ring-warning-500/10">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <x-filament::icon
                            icon="heroicon-o-exclamation-circle"
                            class="h-8 w-8 text-warning-600 flex-shrink-0"
                        />
                        <div>
                            <h3 class="text-xl font-semibold leading-6 text-warning-900 dark:text-warning-100">
                                Set your spare availability
                            </h3>
                            <p class="mt-1 text-sm text-warning-700 dark:text-warning-300">
                                Let others know when you're available to spare
                            </p>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <a
                            href="{{ route('filament.admin.resources.spare-availabilities.create') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-warning-500 transition-colors duration-200"
                        >
                            Set Availability
                            <x-filament::icon
                                icon="heroicon-m-arrow-right"
                                class="h-4 w-4"
                            />
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
