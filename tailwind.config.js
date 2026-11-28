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
                sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            colors: {
                forest: '#0B3D2E',
                panel: '#0B4F3A',
                cream: '#F7F3EA',
                'card-cream': '#FCFAF6',
                'field-border': '#D2DBD5',
                divider: '#E4DDD0',
                'muted-ink': '#53655D',
                primary: {
                    DEFAULT: '#0B6E4F',
                    hover: '#095A41',
                    light: '#E6F4EE',
                },
                accent: {
                    DEFAULT: '#F2B705',
                    hover: '#D9A404',
                    soft: '#FFF6D6',
                },
                ink: '#0F172A',
                body: '#334155',
                muted: '#64748B',
                page: '#F8FAF9',
                card: '#FFFFFF',
                border: '#E2E8F0',
                success: '#16A34A',
                warning: '#F59E0B',
                danger: '#DC2626',
            },
            boxShadow: {
                soft: '0 8px 24px rgba(15, 23, 42, 0.06)',
                'soft-hover': '0 12px 30px rgba(15, 23, 42, 0.1)',
                'auth-offset': '8px 8px 0 #0B3D2E',
            },
        },
    },

    plugins: [forms],
};
