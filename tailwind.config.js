import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#063c96', // Dark blue
                    light: '#5170ff',   // Soft blue
                    accent: '#2281c5',  // Blue
                },
                status: {
                    active: {
                        bg: '#dcfce7',
                        text: '#15803d',
                    },
                    pending: {
                        bg: '#ffedd5',
                        text: '#c2410c',
                    },
                    completed: {
                        bg: '#dcfce7',
                        text: '#15803d',
                    },
                    inactive: {
                        bg: '#fef2f2',
                        text: '#dc2626',
                    },
                },
            },
            fontSize: {
                'xs': '0.75rem',   // 12px
                'sm': '0.875rem',  // 14px
                'base': '1rem',    // 16px
                'lg': '1.125rem',  // 18px
                'xl': '1.25rem',   // 20px
            },
        },
    },

    plugins: [forms],
};
