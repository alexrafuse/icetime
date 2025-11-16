<x-filament-panels::page>
    @php
        $categorizedResources = $this->getResourcesByCategory();


    @endphp

    {{-- Search and Filter Bar --}}
    <div class="mb-8 space-y-4">
        <div class="flex flex-col sm:flex-row gap-4">
            {{-- Search Input --}}
            <div class="flex-1">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search resources by title or description..."
                        class="w-full"
                    >
                        <x-slot name="prefix">
                            <x-filament::icon
                                icon="heroicon-m-magnifying-glass"
                                class="h-5 w-5 text-gray-400"
                            />
                        </x-slot>
                    </x-filament::input>
                </x-filament::input.wrapper>
            </div>

            {{-- Category Filter --}}
            <div class="sm:w-64">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="categoryFilter">
                        <option value="">All Categories</option>
                        @foreach (\App\Domain\Shared\Enums\ResourceCategory::cases() as $category)
                            <option value="{{ $category->value }}">{{ $category->getLabel() }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            {{-- Clear Filters Button --}}
            @if ($search || $categoryFilter)
                <div class="sm:w-auto">
                    <x-filament::button
                        wire:click="clearFilters"
                        color="gray"
                        outlined
                    >
                        Clear Filters
                    </x-filament::button>
                </div>
            @endif
        </div>

        {{-- Active Filters Display --}}
        @if ($search || $categoryFilter)
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <span class="font-medium">Active filters:</span>
                @if ($search)
                    <span class="inline-flex items-center gap-1 rounded-md bg-primary-100 dark:bg-primary-900/30 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-300">
                        Search: "{{ $search }}"
                    </span>
                @endif
                @if ($categoryFilter)
                    <span class="inline-flex items-center gap-1 rounded-md bg-primary-100 dark:bg-primary-900/30 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-300">
                        Category: {{ \App\Domain\Shared\Enums\ResourceCategory::from($categoryFilter)->getLabel() }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    @if (empty($categorizedResources))
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center shadow-sm">
            <x-filament::icon
                icon="heroicon-o-folder"
                class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4"
            />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                No resources available
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                There are currently no resources available. Check back later!
            </p>
        </div>
    @else
        <div class="space-y-10">
            @foreach ($categorizedResources as $categoryData)
                @php
                    $category = $categoryData['category'];
                    $resources = $categoryData['resources'];
                @endphp

                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <x-filament::icon
                            :icon="$category->getIcon()"
                            class="h-8 w-8 text-{{ $category->getColor() }}-600 dark:text-{{ $category->getColor() }}-400"
                        />
                        <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                            {{ $category->getLabel() }}
                        </h2>
                        <span class="inline-flex items-center rounded-full bg-{{ $category->getColor() }}-100 dark:bg-{{ $category->getColor() }}-900/30 px-3 py-1 text-xs font-medium text-{{ $category->getColor() }}-700 dark:text-{{ $category->getColor() }}-300">
                            {{ $resources->count() }} {{ Str::plural('resource', $resources->count()) }}
                        </span>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($resources as $resource)
                            <div class="rounded-xl border border-{{ $category->getColor() }}-200 dark:border-{{ $category->getColor() }}-800 bg-gradient-to-br from-{{ $category->getColor() }}-50 to-white dark:from-{{ $category->getColor() }}-900/20 dark:to-gray-800 p-6 shadow-md hover:shadow-lg transition-all duration-200 flex flex-col group">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <x-filament::icon
                                            :icon="$category->getIcon()"
                                            class="h-10 w-10 text-{{ $category->getColor() }}-600 dark:text-{{ $category->getColor() }}-400 flex-shrink-0 group-hover:scale-110 transition-transform duration-200"
                                        />
                                        @if ($resource->type === 'url')
                                            <x-filament::icon
                                                icon="heroicon-s-link"
                                                class="h-4 w-4 text-info-600 dark:text-info-400"
                                            />
                                        @else
                                            <x-filament::icon
                                                icon="heroicon-s-document"
                                                class="h-4 w-4 text-success-600 dark:text-success-400"
                                            />
                                        @endif
                                    </div>
                                    @if ($resource->priority <= 3)
                                        <span class="inline-flex items-center rounded-md bg-success-100 dark:bg-success-900/50 px-2 py-1 text-xs font-medium text-success-700 dark:text-success-300">
                                            Featured
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 leading-snug">
                                    {{ $resource->title }}
                                </h3>

                                @if ($resource->description)
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-6 leading-relaxed flex-1">
                                        {{ Str::limit($resource->description, 120) }}
                                    </p>
                                @endif

                                <div class="mt-auto">
                                    <a
                                        href="{{ $resource->isUrl() ? $resource->url : $resource->getFileUrl() }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-{{ $category->getColor() }}-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-{{ $category->getColor() }}-500 transition-colors duration-200"
                                    >
                                        @if ($resource->isUrl())
                                            Open Link
                                            <x-filament::icon
                                                icon="heroicon-m-arrow-top-right-on-square"
                                                class="h-4 w-4"
                                            />
                                        @else
                                            View File
                                            <x-filament::icon
                                                icon="heroicon-m-arrow-down-tray"
                                                class="h-4 w-4"
                                            />
                                        @endif
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
