/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
        './app/View/Components/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                // Single brand scale — every card/badge/chart in the ERP
                // pulls from this instead of ad-hoc hex values, so Settings
                // → branding can one day re-theme the whole app from here.
                // Kept as-is (do not rename/remove — ~165 existing usages
                // across 159 views depend on it). New work should prefer
                // `accent` below; `brand` stays for backward compat only.
                brand: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
                // Phase 13 design-system foundation ------------------------
                // Page/elevated-surface backgrounds (warm off-white, not
                // Tailwind's cool gray-50/white pairing).
                paper: {
                    DEFAULT: '#FAF8F4',
                },
                surface: {
                    DEFAULT: '#FFFFFF',
                },
                // Warm-neutral text/border scale anchored on #20241F (900).
                // A deliberate warm gray ramp — not Tailwind's default
                // blue-tinted `gray` — used by every new component this
                // phase introduces.
                ink: {
                    50: '#F7F6F4',
                    100: '#EEECE8',
                    200: '#DCD9D2',
                    300: '#C2BDB2',
                    400: '#9D9689',
                    500: '#7A7468',
                    600: '#5C564C',
                    700: '#454138',
                    800: '#2F2D27',
                    900: '#20241F',
                },
                // New primary brand color going forward: deep architectural
                // teal-green, anchored on #2B6157 as the 600 "primary
                // button" shade.
                accent: {
                    50: '#EBF4F1',
                    100: '#D3E7E1',
                    200: '#A8CFC3',
                    300: '#7DB7A5',
                    400: '#529F87',
                    500: '#3A8370',
                    600: '#2B6157',
                    700: '#224E46',
                    800: '#1A3B36',
                    900: '#112825',
                },
                // Optional cleaner semantics for new components (existing
                // red-*/green-*/yellow-* Tailwind defaults remain in use
                // elsewhere and are untouched).
                success: {
                    50: '#EFF8EF',
                    100: '#D9EDD9',
                    200: '#AEDAAF',
                    300: '#83C684',
                    400: '#57B259',
                    500: '#2E9830',
                    600: '#15803D',
                    700: '#126A33',
                    800: '#0E5329',
                    900: '#0A3D1F',
                },
                warning: {
                    50: '#FDF4E9',
                    100: '#FAE4C3',
                    200: '#F4C987',
                    300: '#EEAE4B',
                    400: '#D88F1F',
                    500: '#C27B0D',
                    600: '#B45309',
                    700: '#8F4208',
                    800: '#6B3106',
                    900: '#472104',
                },
                danger: {
                    50: '#FBEAE9',
                    100: '#F5CBC8',
                    200: '#E9978F',
                    300: '#DD6356',
                    400: '#CB3B2B',
                    500: '#C02A1E',
                    600: '#B3261E',
                    700: '#8F1E18',
                    800: '#6B1612',
                    900: '#470F0C',
                },
            },
            fontFamily: {
                sans: ['Manrope', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                serif: ['Fraunces', 'ui-serif', 'Georgia', 'serif'],
            },
            borderRadius: {
                sm: '6px',
                DEFAULT: '8px',
                md: '8px',
                lg: '12px',
            },
        },
    },
    plugins: [require('@tailwindcss/forms')],
};
