import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './app/Filament/**/*.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './resources/views/filament/widgets/*.blade.php'



    ],
    safelist: [
        {
            pattern: /grid-cols-(1|2|3|4|5|6|7)/,
        },
        {
            pattern: /mb-(0|1|2|3|4|5|6|7|8|10|12)/,
        },
        {
            pattern: /space-y-(1|2|3|4)/,
        },
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            borderWidth: {
                '3': '3px',
            },
        },
    },
    plugins: [],
};
