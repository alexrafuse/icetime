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
            
            const filteredResources = this.getFilteredResources();
            this.calendar.setOption('resources', filteredResources);
            this.calendar.refetchEvents();
        },
        
        isAreaActive(areaId) {
            return this.activeAreas.includes(Number(areaId));
        }
    }" x-init="initializeCalendar()">
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <button
                id="create-booking-button"
                class="hidden fixed bottom-4 right-4 z-50 inline-flex items-center justify-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-medium text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition"
                x-data
                @click="
                    const button = $el;
                    const url = '/admin/bookings/create?' + new URLSearchParams({
                        date: button.dataset.date,
                        start_time: button.dataset.startTime,
                        end_time: button.dataset.endTime,
                        areas: button.dataset.resources
                    });
                    window.location.href = url;
                "
            >
                Create Booking
            </button>   
        
        <div id="calendar"></div>
            
           
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
