import { Calendar } from '@fullcalendar/core';
import resourceTimeGrid from '@fullcalendar/resource-timegrid';
import dayGrid from '@fullcalendar/daygrid';
import timeGrid from '@fullcalendar/timegrid';
import interaction from '@fullcalendar/interaction';

window.initializeCalendar = function(bookings, areas) {
    const calendarEl = document.getElementById('calendar');
    
    const calendar = new Calendar(calendarEl, {
        plugins: [resourceTimeGrid, dayGrid, timeGrid, interaction],
        initialView: 'resourceTimeGridWeek',
        events: bookings,
        resources: areas,
        slotMinTime: '08:00:00',
        slotMaxTime: '23:00:00',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'resourceTimeGridDay,resourceTimeGridWeek,dayGridMonth'
        },
        resourceGroupField: 'building',
        resourceAreaWidth: '150px',
        resourceAreaColumns: [{
            field: 'title',
            headerContent: 'Areas'
        }],
        eventDidMount: function(info) {
            const event = info.event;
            const props = event.extendedProps;
            
            // Create tooltip content
            info.el.title = `
                ${event.title}
                Type: ${props.event_type}
                Status: ${props.payment_status}
                Areas: ${props.areas}
                ${props.setup_instructions ? `Setup: ${props.setup_instructions}` : ''}
            `.trim();
        }
    });

    calendar.render();
}; 