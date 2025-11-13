import { Calendar } from '@fullcalendar/core';
import resourceTimeGrid from '@fullcalendar/resource-timegrid';
import dayGrid from '@fullcalendar/daygrid';
import timeGrid from '@fullcalendar/timegrid';
import interaction from '@fullcalendar/interaction';

function initializeCalendar(bookings, areas, viewMode = 'area') {
    const calendarEl = document.getElementById('calendar');

    console.log('Initializing calendar with:', { bookings, areas, viewMode });

    // Determine initial view and configuration based on viewMode
    const isAreaView = viewMode === 'area';
    const initialView = isAreaView ? 'resourceTimeGridWeek' : 'timeGridWeek';

    // Base configuration shared by both views
    const baseConfig = {
        plugins: [resourceTimeGrid, dayGrid, timeGrid, interaction],
        initialView: initialView,
        events: bookings,
        slotMinTime: '08:00:00',
        slotMaxTime: '23:00:00',
        slotDuration: '00:30:00',
        slotLabelInterval: '01:00:00',
        expandRows: false,
        contentHeight: 'auto',
        height: 'auto',
        allDaySlot: false,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: isAreaView
                ? 'resourceTimeGridDay,resourceTimeGridWeek,dayGridMonth'
                : 'timeGridDay,timeGridWeek,dayGridMonth'
        },
        selectable: true,
        selectMirror: true,
        selectMinDistance: 5,
        selectConstraint: {
            startTime: '08:00:00',
            endTime: '23:00:00',
        },
        select: function(info) {
            console.log('Selection made:', info);
            const bookingButton = document.getElementById('create-booking-button');
            if (bookingButton) {
                // Get the resource ID from the selection
                const resourceId = info.resource ? info.resource.id :
                                  (info.resources && info.resources.length > 0 ? info.resources[0].id : '');

                // Update the button's data attributes
                bookingButton.dataset.date = info.start.toISOString().split('T')[0];
                bookingButton.dataset.startTime = info.start.toTimeString().split(' ')[0];
                bookingButton.dataset.endTime = info.end.toTimeString().split(' ')[0];
                bookingButton.dataset.resourceId = resourceId; // Store the resource ID

                // Only show the button if we have a resource selected
                if (resourceId) {
                    bookingButton.classList.remove('hidden');

                    // Update to use the Filament admin route
                    bookingButton.addEventListener('click', () => {
                        const params = new URLSearchParams({
                            date: bookingButton.dataset.date,
                            start_time: bookingButton.dataset.startTime,
                            end_time: bookingButton.dataset.endTime,
                            areas: bookingButton.dataset.resourceId
                        });

                        window.location.href = `/bookings/create?${params.toString()}`;
                    }, { once: true });
                }
            }
        },
        unselect: function(info) {
            console.log('Selection cleared');
            const bookingButton = document.getElementById('create-booking-button');
            if (bookingButton) {
                bookingButton.classList.add('hidden');
            }
        },
        selectOverlap: false,
        eventClick: function(info) {
            // Extract the booking ID from the event
            const bookingId = info.event.id;
            // Navigate to the booking's show page in Filament
            window.location.href = `/bookings/${bookingId}/edit`;
        },
        eventDidMount: function(info) {
            const event = info.event;
            const props = event.extendedProps;

            info.el.title = `
                ${event.title}
                Type: ${props.event_type}
                Status: ${props.payment_status}
                Areas: ${props.areas}
                ${props.setup_instructions ? `Setup: ${props.setup_instructions}` : ''}
            `.trim();
        },
        viewDidMount: function(info) {
            // Add "Booking Calendar: " prefix to the title
            const titleElement = document.querySelector('.fc-toolbar-title');
            if (titleElement && !titleElement.textContent.startsWith('Booking Calendar:')) {
                titleElement.textContent = 'Booking Calendar: ' + titleElement.textContent;
            }
        },
        datesSet: function(info) {
            // Update title when dates change (navigation)
            const titleElement = document.querySelector('.fc-toolbar-title');
            if (titleElement && !titleElement.textContent.startsWith('Booking Calendar:')) {
                titleElement.textContent = 'Booking Calendar: ' + titleElement.textContent;
            }
        },
    };

    // Add resource-specific configuration only for Area View
    if (isAreaView) {
        baseConfig.resources = areas;
        baseConfig.resourceAreaWidth = '100px';
        baseConfig.resourceAreaColumns = [{
            field: 'title',
            headerContent: 'Areas'
        }];
        baseConfig.datesAboveResources = true;
        baseConfig.resourceOrder = 'title';
        baseConfig.resourcesInitiallyExpanded = true;
    }

    const calendar = new Calendar(calendarEl, baseConfig);

    calendar.render();
    return calendar;
}

window.initializeCalendar = initializeCalendar;
