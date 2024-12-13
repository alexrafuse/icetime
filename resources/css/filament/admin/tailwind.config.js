export default {
    content: [
        // ...
        './vendor/saade/filament-fullcalendar/resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                'calendar-private': '#4ade80',
                'calendar-league': '#3b82f6',
                'calendar-tournament': '#f97316',
            },
        },
    },
}; 