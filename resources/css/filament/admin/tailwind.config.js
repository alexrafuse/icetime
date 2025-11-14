import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
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
    }
}
