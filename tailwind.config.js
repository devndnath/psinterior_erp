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
                // Re-pointed to the same gold ramp as `accent` so legacy
                // `brand-*` usages pick up the PS Solution branding too
                // (website analysis — see `accent` below for sourcing).
                brand: {
                    50: '#FEFBEA',
                    100: '#FDF3C4',
                    200: '#FBE587',
                    300: '#F7D24A',
                    400: '#F4C01C',
                    500: '#F4B400',
                    600: '#D4A000',
                    700: '#A67D00',
                    800: '#785C00',
                    900: '#4D3A00',
                },
                // Phase 13 design-system foundation ------------------------
                // Page/elevated-surface backgrounds (warm off-white, not
                // Tailwind's cool gray-50/white pairing). Tightened slightly
                // (was #FAF8F4) to sit closer to pssolutionbd.com's own
                // cream background (#FAFAF7, extracted from the live site).
                paper: {
                    DEFAULT: '#FAFAF7',
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
                // Primary brand color re-themed to match pssolutionbd.com's
                // actual branding (extracted via computed-style inspection
                // of the live site, not guessed): a warm gold anchored on
                // the site's own CTA/nav-highlight gold `#F4B400` (500) and
                // its darker hover shade `#D4A000` (600). 700–900 are pulled
                // noticeably darker/browner than the site's own gold so
                // `text-accent-700/800` keeps enough contrast on white for
                // buttons, links and badge text — the site itself never
                // needs gold text on white, the ERP does.
                accent: {
                    50: '#FEFBEA',
                    100: '#FDF3C4',
                    200: '#FBE587',
                    300: '#F7D24A',
                    400: '#F4C01C',
                    500: '#F4B400',
                    600: '#D4A000',
                    700: '#A67D00',
                    800: '#785C00',
                    900: '#4D3A00',
                },
                // Optional cleaner semantics for new components (existing
                // red-*/green-*/yellow-* Tailwind defaults remain in use
                // elsewhere and are untouched). Retuned to Tailwind's
                // standard emerald scale, anchored on `#059669` (600) to
                // match the emerald "success/positive" accent used on
                // pssolutionbd.com's own site.
                success: {
                    50: '#ECFDF5',
                    100: '#D1FAE5',
                    200: '#A7F3D0',
                    300: '#6EE7B7',
                    400: '#34D399',
                    500: '#10B981',
                    600: '#059669',
                    700: '#047857',
                    800: '#065F46',
                    900: '#064E3B',
                },
                // Shifted from the old yellow-gold ramp to orange — now that
                // `accent` itself is gold, `warning` needs a hue that stays
                // visually distinct from "primary brand action" at a glance.
                warning: {
                    50: '#FFF7ED',
                    100: '#FFEDD5',
                    200: '#FED7AA',
                    300: '#FDBA74',
                    400: '#FB923C',
                    500: '#F97316',
                    600: '#EA580C',
                    700: '#C2410C',
                    800: '#9A3412',
                    900: '#7C2D12',
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
