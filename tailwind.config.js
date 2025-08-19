import preset from './vendor/filament/support/tailwind.config.preset'
import forms from '@tailwindcss/forms'

/** @type {import('tailwindcss').Config} */
export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './vendor/filament/**/*.blade.php',
        './vendor/awcodes/filament-table-repeater/resources/**/*.blade.php',
        './resources/css/**/*.css',
    ],
    theme: {
        extend: {},
    },
    darkMode: 'media',
    plugins: [forms],
    safelist: [
        {
            pattern: /^(bg|text)-(green|yellow|red|gray)-(50|200|600|900)$/,
            variants: ['dark'],
        },
        {
            pattern: /^ring-(green|yellow|red|gray)-(200|600)\/10$/,
            variants: ['dark'],
        },
    ]
}

