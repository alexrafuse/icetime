<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-4 gap-6">
            <x-filament::card>
                <div class="text-sm font-medium text-gray-500">Total Bookings</div>
                <div class="text-2xl font-bold">{{ $bookings->count() }}</div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-sm font-medium text-gray-500">Private Events</div>
                <div class="text-2xl font-bold">{{ $bookings->where('event_type', 'private')->count() }}</div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-sm font-medium text-gray-500">League Events</div>
                <div class="text-2xl font-bold">{{ $bookings->where('event_type', 'league')->count() }}</div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-sm font-medium text-gray-500">Tournaments</div>
                <div class="text-2xl font-bold">{{ $bookings->where('event_type', 'tournament')->count() }}</div>
            </x-filament::card>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div id="calendar"></div>
        </div>
    </div>

    @push('scripts')
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
        <script src="https://unpkg.com/@popperjs/core@2"></script>
        <script src="https://unpkg.com/tippy.js@6"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const calendarEl = document.getElementById('calendar');
                const bookings = @json($bookings);
                
                const formattedEvents = bookings.map(booking => ({
                    id: booking.id,
                    title: booking.title,
                    start: booking.start,
                    end: booking.end,
                    backgroundColor: booking.backgroundColor,
                    borderColor: booking.borderColor,
                    extendedProps: {
                        areas: booking.extendedProps.areas,
                        event_type: booking.extendedProps.event_type,
                        payment_status: booking.extendedProps.payment_status,
                        setup_instructions: booking.extendedProps.setup_instructions
                    }
                }));

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'timeGridWeek',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    slotMinTime: '08:00:00',
                    slotMaxTime: '23:00:00',
                    schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
                    resources: @json($areas),
                    events: formattedEvents,
                    resourceAreaWidth: '150px',
                    eventDidMount: function(info) {
                        tippy(info.el, {
                            content: `
                                <div class="p-2">
                                    <div class="font-medium">${info.event.title}</div>
                                    <div class="text-sm text-gray-500">${info.event.extendedProps.areas}</div>
                                    <div class="text-sm text-gray-500">${info.event.extendedProps.event_type}</div>
                                    <div class="text-sm text-gray-500">Status: ${info.event.extendedProps.payment_status}</div>
                                    ${info.event.extendedProps.setup_instructions ? `
                                        <div class="text-sm text-gray-500 mt-1">
                                            <span class="font-medium">Setup:</span> ${info.event.extendedProps.setup_instructions}
                                        </div>
                                    ` : ''}
                                </div>
                            `,
                            allowHTML: true,
                            theme: 'light',
                        });
                    },
                });

                function getEventColor(eventType) {
                    const colors = {
                        private: '#4f46e5',    // Indigo
                        league: '#059669',     // Emerald
                        tournament: '#dc2626'  // Red
                    };
                    return colors[eventType] || '#6b7280'; // Default gray
                }

                calendar.render();
            });
        </script>
    @endpush

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light.css"/>
        <style>
            .fc-event {
                cursor: pointer;
            }
            .fc-day-today {
                background-color: rgb(243 244 246) !important;
            }
            .dark .fc-day-today {
                background-color: rgb(31 41 55) !important;
            }
            .fc .fc-toolbar {
                padding: 1rem;
            }
            .fc .fc-view-harness {
                padding: 0 1rem 1rem 1rem;
            }
        </style>
    @endpush
</x-filament-panels::page>
