import { Calendar } from '@fullcalendar/core';
import dayGrid from '@fullcalendar/daygrid';
import timeGrid from '@fullcalendar/timegrid';

const NARROW_WIDTH = 640;
const VIEWS = { day: 'timeGridDay', week: 'timeGridWeek', month: 'dayGridMonth' };

const el = document.getElementById('ice-calendar');
const requestedView = VIEWS[new URLSearchParams(window.location.search).get('view')];
const isNarrow = () => window.innerWidth < NARROW_WIDTH;
const weekOrDay = () => (isNarrow() ? VIEWS.day : VIEWS.week);

const sheetCount = Number(el.dataset.sheetCount);
const sheetLabel = (sheets) => (sheets.length === sheetCount ? 'All sheets' : sheets.join(', '));

const escapeHtml = (text) => {
    const span = document.createElement('span');
    span.textContent = text;
    return span.innerHTML;
};

const calendar = new Calendar(el, {
    plugins: [dayGrid, timeGrid],
    initialView: requestedView ?? weekOrDay(),
    events: { url: el.dataset.feedUrl, success: (response) => response.data },
    height: '100%',
    allDaySlot: false,
    nowIndicator: true,
    slotMinTime: '08:00:00',
    slotMaxTime: '23:00:00',
    slotDuration: '00:30:00',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'timeGridDay,timeGridWeek,dayGridMonth',
    },
    eventContent: ({ event, timeText }) => {
        const sheets = sheetLabel(event.extendedProps.sheets);

        return {
            html: `<div class="ice-event">
                <div class="ice-event-time">${escapeHtml(timeText)}</div>
                <div class="ice-event-title">${escapeHtml(event.title)}</div>
                <div class="ice-event-sheets">${escapeHtml(sheets)}</div>
            </div>`,
        };
    },
    eventDidMount: ({ el: eventEl, event }) => {
        eventEl.title = [event.title, event.extendedProps.event_type, sheetLabel(event.extendedProps.sheets)].join('\n');
    },
    windowResize: () => {
        if (requestedView || calendar.view.type === VIEWS.month) {
            return;
        }

        calendar.changeView(weekOrDay());
    },
    loading: (isLoading) => el.classList.toggle('is-loading', isLoading),
});

calendar.render();
