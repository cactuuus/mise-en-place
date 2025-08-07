import preset from './vendor/filament/support/tailwind.config.preset'
import forms from '@tailwindcss/forms'

/** @type {import('tailwindcss').Config} */
export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/awcodes/filament-table-repeater/resources/**/*.blade.php',
        './resources/css/**/*.css',
    ],
    theme: {
        extend: {},
    },
    plugins: [forms],
}

