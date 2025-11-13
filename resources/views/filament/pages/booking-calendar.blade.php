<x-filament-panels::page>
    <div class="space-y-6" x-data="{
        calendar: null,
        bookings: {{ Js::from($bookings) }},
        areas: {{ Js::from($areas) }},
        activeAreas: [],
        viewMode: 'overall',

        initializeCalendar() {
            this.activeAreas = this.areas
                .filter(area => ['Sheet A', 'Sheet B', 'Sheet C', 'Sheet D'].includes(area.title))
                .map(area => Number(area.id));


            this.calendar = initializeCalendar(
                this.bookings,
                this.getFilteredResources(),
                this.viewMode
            );

            console.log('Calendar initialized with active areas:', this.activeAreas);
        },

        getFilteredResources() {
            return this.areas.filter(area => this.activeAreas.includes(Number(area.id)));
        },

        toggleArea(areaId) {
            areaId = Number(areaId);
            const index = this.activeAreas.indexOf(areaId);

            if (index > -1) {
                this.activeAreas.splice(index, 1);
            } else {
                this.activeAreas.push(areaId);
            }

            this.calendar.setOption('resources', this.getFilteredResources());
            this.calendar.render();
        },

        isAreaActive(areaId) {
            return this.activeAreas.includes(Number(areaId));
        },

        switchViewMode(mode) {
            this.viewMode = mode;
            if (this.calendar) {
                // Destroy and recreate calendar to properly switch view mode
                this.calendar.destroy();
                this.calendar = initializeCalendar(
                    this.bookings,
                    this.getFilteredResources(),
                    mode
                );
            }
        }
    }" x-init="initializeCalendar()">
        <!-- Calendar Container with Horizontal Scroll -->
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto overflow-y-hidden">
                <div style="min-width: 600px;">
                    <!-- View Mode Toggle and Area Filters (Above Calendar) -->
                    <div class="bg-white dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- View Mode Toggle -->
                            <div class="flex items-center gap-2">
                                <div class="inline-flex rounded-md border border-gray-300 dark:border-gray-600 p-0.5 bg-gray-50 dark:bg-gray-900">
                                    <button type="button"
                                        @click="switchViewMode('area')"
                                        :class="{
                                            'bg-white dark:bg-gray-700 shadow-sm': viewMode === 'area',
                                            'hover:bg-gray-100 dark:hover:bg-gray-800': viewMode !== 'area'
                                        }"
                                        class="px-2.5 py-0.5 text-xs font-medium rounded transition-all duration-200 text-gray-700 dark:text-gray-300">
                                        Area View
                                    </button>
                                    <button type="button"
                                        @click="switchViewMode('overall')"
                                        :class="{
                                            'bg-white dark:bg-gray-700 shadow-sm': viewMode === 'overall',
                                            'hover:bg-gray-100 dark:hover:bg-gray-800': viewMode !== 'overall'
                                        }"
                                        class="px-2.5 py-0.5 text-xs font-medium rounded transition-all duration-200 text-gray-700 dark:text-gray-300">
                                        Overall View
                                    </button>
                                </div>
                            </div>

                            <!-- Area Filters (only visible in Area View) -->
                            <div x-show="viewMode === 'area'" class="flex flex-wrap items-center gap-1.5" x-transition>
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Filter:</span>
                                @foreach($areas as $area)
                                <button type="button"
                                    class="area-toggle inline-flex items-center px-2 py-0.5 text-xs font-medium rounded transition-all duration-200 border"
                                    x-on:click="toggleArea('{{ $area['id'] }}')"
                                    :class="{
                                        'bg-primary-600 text-white border-primary-600 shadow-sm': isAreaActive('{{ $area['id'] }}'),
                                        'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700': !isAreaActive('{{ $area['id'] }}')
                                    }">
                                    <span
                                        class="w-1.5 h-1.5 mr-1 rounded-full transition-colors"
                                        :class="{
                                            'bg-white': isAreaActive('{{ $area['id'] }}'),
                                            'bg-gray-400 dark:bg-gray-500': !isAreaActive('{{ $area['id'] }}')
                                        }"></span>
                                    {{ $area['title'] }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div id="calendar"></div>
                </div>
            </div>
        </div>

        <!-- Create Booking Button -->
        <button
            id="create-booking-button"
            class="hidden fixed bottom-4 right-4 z-50 inline-flex items-center justify-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-medium text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition"
            x-data
            @click="
                const button = $el;
                const url = '/bookings/create?' + new URLSearchParams({
                    date: button.dataset.date,
                    start_time: button.dataset.startTime,
                    end_time: button.dataset.endTime,
                    areas: button.dataset.resources
                });
                window.location.href = url;
            ">
            Create Booking
        </button>
    </div>


    <script>



    </script>

    @push('scripts')
    @vite(['resources/js/calendar.js'])
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    @endpush

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
    <style>
        .fc-event {
            cursor: pointer;
        }

        /* Make toolbar more compact */
        .fc .fc-toolbar {
            padding: 0.5rem 1rem;
            gap: 0.5rem;
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            line-height: 1.5rem;
        }

        .fc .fc-button {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .fc .fc-view-harness {
            padding: 0 1rem 1rem 1rem;
        }

        /* Reduce timeslot height */
        .fc .fc-timegrid-slot {
            height: 1rem !important;
        }

        .fc .fc-timegrid-slot-label {
            font-size: 0.65rem;
            vertical-align: top;
            padding-top: 0.125rem;
        }

        /* More compact column headers */
        .fc .fc-col-header-cell {
            padding: 0.25rem;
            font-size: 0.875rem;
        }

        /* Narrower day columns */
        .fc .fc-col-header-cell,
        .fc .fc-timegrid-col {
            min-width: 100px;
        }

        .fc .fc-daygrid-day {
            min-width: 120px;
        }

        /* Compact resource area */
        .fc .fc-datagrid-cell {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Narrower resource columns in area view */
        .fc-direction-ltr .fc-datagrid-body {
            width: 100px !important;
        }

        .fc-resource-group {
            font-weight: bold;
            background-color: #f8fafc;
        }

        .area-toggle {
            transform: translateY(0);
        }

        .area-toggle:hover {
            transform: translateY(-1px);
        }

        .area-toggle:active {
            transform: translateY(0);
        }

        /* Horizontal scroll indicator */
        .overflow-x-auto {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
        }

        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: transparent;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background-color: rgba(156, 163, 175, 0.7);
        }

        /* Scroll shadow indicators */
        .overflow-x-auto {
            background:
                linear-gradient(90deg, white 0%, transparent 10px),
                linear-gradient(270deg, white 0%, transparent 10px),
                linear-gradient(90deg, rgba(0,0,0,0.1) 0%, transparent 20px),
                linear-gradient(270deg, rgba(0,0,0,0.1) 0%, transparent 20px);
            background-repeat: no-repeat;
            background-size: 10px 100%, 10px 100%, 20px 100%, 20px 100%;
            background-position: 0 0, 100% 0, 0 0, 100% 0;
            background-attachment: local, local, scroll, scroll;
        }

        .dark .overflow-x-auto {
            background:
                linear-gradient(90deg, rgb(31, 41, 55) 0%, transparent 10px),
                linear-gradient(270deg, rgb(31, 41, 55) 0%, transparent 10px),
                linear-gradient(90deg, rgba(255,255,255,0.1) 0%, transparent 20px),
                linear-gradient(270deg, rgba(255,255,255,0.1) 0%, transparent 20px);
            background-repeat: no-repeat;
            background-size: 10px 100%, 10px 100%, 20px 100%, 20px 100%;
            background-position: 0 0, 100% 0, 0 0, 100% 0;
            background-attachment: local, local, scroll, scroll;
        }
    </style>
    @endpush
</x-filament-panels::page>
