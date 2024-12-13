<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('filter-calendar', ({ areaIds, eventType }) => {
        const calendar = document.querySelector('[data-calendar]')?._fullCalendar;
        if (!calendar) return;

        calendar.getEvents().forEach(event => {
            let visible = true;

            if (areaIds?.length) {
                const eventAreas = event.extendedProps.areas || [];
                visible = eventAreas.some(area => areaIds.includes(area));
            }

            if (eventType && visible) {
                visible = event.extendedProps.event_type === eventType;
            }

            event.setDisplay(visible ? 'auto' : 'none');
        });
    });
});
</script> 