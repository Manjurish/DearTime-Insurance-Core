const colors = require('tailwindcss/colors');

module.exports = {
    purge: {
        enabled: process.env.PURGE_CSS === 'production' ? true : false,
        content: [
            './vendor/laravel/jetstream/**/*.blade.php',
            './storage/framework/views/*.php',
            './resources/views/**/*.blade.php',
            './resources/js/**/*.vue',
        ],
    },
    darkMode: false, // or 'media' or 'class'
    theme: {
        extend: {},
    },
    variants: {
        textColor: ['responsive', 'hover', 'focus', 'group-hover'],
        extend: {
        },
    },
    plugins: [],
}
