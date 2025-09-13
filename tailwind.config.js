import {colors} from './resources/js/colors.ts'

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{vue,js,ts}',
        './resources/css/**/*.css',
    ],
    theme: {
        extend: {
            colors
        },
    },
    safelist: [
        {
            pattern: /^(bg|text)-(green|yellow|red|gray)-(50|200|600|900)$/,
            variants: ['dark'],
        }
    ],
    darkMode: 'media',
    plugins: [],
}
