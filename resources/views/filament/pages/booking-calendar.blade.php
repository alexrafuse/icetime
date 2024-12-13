<x-filament-panels::page>
    <div class="space-y-6">
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

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div id="calendar"></div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/calendar.js'])
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof initializeCalendar === 'function') {
                    const bookings = @json($bookings);
                    const areas = @json($areas);
                    console.log('Bookings:', bookings); // For debugging
                    console.log('Areas:', areas); // For debugging
                    initializeCalendar(bookings, areas);
                } else {
                    console.error('Calendar initialization function not found');
                }
            });
        </script>
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
        </style>
    @endpush
</x-filament-panels::page>
