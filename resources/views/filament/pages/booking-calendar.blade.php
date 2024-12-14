<x-filament-panels::page>
    <div class="space-y-6" x-data="{
        calendar: null,
        bookings: {{ Js::from($bookings) }},
        areas: {{ Js::from($areas) }},
        activeAreas: [],
        
        initializeCalendar() {
            this.activeAreas = this.areas
                .filter(area => ['Sheet A', 'Sheet B', 'Sheet C', 'Sheet D'].includes(area.title))
                .map(area => Number(area.id));
            
            this.calendar = initializeCalendar(
                this.bookings,
                this.getFilteredResources()
            );
        },
        
        getFilteredResources() {
            return this.areas.filter(area => this.activeAreas.includes(Number(area.id)));
        },
        
        toggleArea(areaId) {
            areaId = Number(areaId);
            console.log('Toggling area:', areaId, typeof areaId);
            
            const index = this.activeAreas.indexOf(areaId);
            
            if (index > -1) {
                this.activeAreas.splice(index, 1);
            } else {
                this.activeAreas.push(areaId);
            }
            
            console.log('Active areas:', this.activeAreas.map(id => typeof id));
            
            const filteredResources = this.getFilteredResources();
            
            this.calendar.setOption('resources', filteredResources);
            
            this.calendar.refetchEvents();
            this.calendar.render();
        },
        
        isAreaActive(areaId) {
            return this.activeAreas.includes(Number(areaId));
        }
    }" x-init="initializeCalendar()">
    

        <div class="flex flex-wrap gap-3 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-500 flex items-center">Filter Areas:</div>
            @foreach($areas as $area)
                <button
                    type="button"
                    class="area-toggle inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 border"
                    x-on:click="toggleArea('{{ $area['id'] }}')"
                    :class="{
                        'bg-primary-600 text-white border-primary-600 shadow-sm': isAreaActive('{{ $area['id'] }}'),
                        'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700': !isAreaActive('{{ $area['id'] }}')
                    }"
                >
                    <span 
                        class="w-2.5 h-2.5 mr-2 rounded-full transition-colors"
                        :class="{
                            'bg-white': isAreaActive('{{ $area['id'] }}'),
                            'bg-gray-400 dark:bg-gray-500': !isAreaActive('{{ $area['id'] }}')
                        }"
                    ></span>
                    {{ $area['title'] }}
                </button>
            @endforeach
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div id="calendar"></div>
        </div>

        <div class="grid grid-cols-4 gap-6">
            <x-filament::card>
                <div class="text-sm font-medium text-gray-500">Total Bookings</div>
                <div class="text-2xl font-bold">{{ collect($bookings)->count() }}</div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-sm font-medium text-gray-500">Private Events</div>
                <div class="text-2xl font-bold">{{ collect($bookings)->where('event_type', 'private')->count() }}</div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-sm font-medium text-gray-500">League Events</div>
                <div class="text-2xl font-bold">{{ collect($bookings)->where('event_type', 'league')->count() }}</div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-sm font-medium text-gray-500">Tournaments</div>
                <div class="text-2xl font-bold">{{ collect($bookings)->where('event_type', 'tournament')->count() }}</div>
            </x-filament::card>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/calendar.js'])
    @endpush

    @push('styles')
        <style>
            .fc-event {
                cursor: pointer;
            }
            .fc .fc-toolbar {
                padding: 1rem;
            }
            .fc .fc-view-harness {
                padding: 0 1rem 1rem 1rem;
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
        </style>
    @endpush
</x-filament-panels::page>
