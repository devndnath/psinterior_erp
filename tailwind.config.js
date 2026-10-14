/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
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
                // Premium-SaaS redesign pass: re-pointed from the PS
                // Solution gold to the same deep-teal ramp as `accent`
                // (see `accent` below for sourcing) so legacy `brand-*`
                // usages — the two portal headers' logo chip and the
                // guest/login layout — stay visually consistent with the
                // rest of the rebrand instead of being the one leftover
                // gold element on screen.
                brand: {
                    50: '#EAF6F5',
                    100: '#D1EBE9',
                    200: '#A3D7D3',
                    300: '#74C2BC',
                    400: '#3CA39C',
                    500: '#1F8D86',
                    600: '#167D78',
                    700: '#126863',
                    800: '#0E514D',
                    900: '#0A3A37',
                },
                // Phase 13 design-system foundation ------------------------
                // Page/elevated-surface backgrounds (warm off-white, not
                // Tailwind's cool gray-50/white pairing). Tightened slightly
                // (was #FAF8F4) to sit closer to pssolutionbd.com's own
                // cream background (#FAFAF7, extracted from the live site).
                //
                // Dark-mode pass: these (plus `ink` and `gray` below) are
                // now CSS-variable-backed instead of static hex, with the
                // variables themselves defined in resources/css/app.css
                // under `:root` (light) and `.dark` (dark). That lets the
                // existing bg-surface/text-ink-900/bg-paper classes already
                // used across all ~160 views re-color automatically when
                // `.dark` is toggled on <html> — no per-view edits needed.
                // The `rgb(var(...) / <alpha-value>)` form preserves
                // Tailwind's opacity-modifier syntax (e.g. bg-surface/90,
                // bg-ink-900/60) exactly as it worked with static hex.
                paper: {
                    DEFAULT: 'rgb(var(--color-paper) / <alpha-value>)',
                },
                surface: {
                    DEFAULT: 'rgb(var(--color-surface) / <alpha-value>)',
                },
                // Warm-neutral text/border scale anchored on #20241F (900).
                // A deliberate warm gray ramp — not Tailwind's default
                // blue-tinted `gray` — used by every new component this
                // phase introduces. See the dark-mode note above `paper`:
                // each shade keeps its semantic role (50/100 = subtlest
                // background tint, 900 = highest-contrast text) in both
                // themes, but the actual values flip in `.dark` so
                // text-ink-900 stays "primary text on this surface" rather
                // than literally staying near-black.
                ink: {
                    50: 'rgb(var(--color-ink-50) / <alpha-value>)',
                    100: 'rgb(var(--color-ink-100) / <alpha-value>)',
                    200: 'rgb(var(--color-ink-200) / <alpha-value>)',
                    300: 'rgb(var(--color-ink-300) / <alpha-value>)',
                    400: 'rgb(var(--color-ink-400) / <alpha-value>)',
                    500: 'rgb(var(--color-ink-500) / <alpha-value>)',
                    600: 'rgb(var(--color-ink-600) / <alpha-value>)',
                    700: 'rgb(var(--color-ink-700) / <alpha-value>)',
                    800: 'rgb(var(--color-ink-800) / <alpha-value>)',
                    900: 'rgb(var(--color-ink-900) / <alpha-value>)',
                },
                // Pre-Phase-13 views (and the two portal layouts) still use
                // Tailwind's stock `gray-*` scale directly instead of the
                // `ink-*` design-system scale above. Re-pointed to the same
                // variables as `ink` (same 50→900 semantic ramp) purely so
                // those older views also go dark automatically — this does
                // NOT change their light-mode appearance at all, since the
                // light-mode variable values below match the ink scale's
                // original hex values exactly.
                gray: {
                    50: 'rgb(var(--color-ink-50) / <alpha-value>)',
                    100: 'rgb(var(--color-ink-100) / <alpha-value>)',
                    200: 'rgb(var(--color-ink-200) / <alpha-value>)',
                    300: 'rgb(var(--color-ink-300) / <alpha-value>)',
                    400: 'rgb(var(--color-ink-400) / <alpha-value>)',
                    500: 'rgb(var(--color-ink-500) / <alpha-value>)',
                    600: 'rgb(var(--color-ink-600) / <alpha-value>)',
                    700: 'rgb(var(--color-ink-700) / <alpha-value>)',
                    800: 'rgb(var(--color-ink-800) / <alpha-value>)',
                    900: 'rgb(var(--color-ink-900) / <alpha-value>)',
                },
                // Premium-SaaS redesign pass (per the uploaded design spec):
                // moved off the PS Solution gold onto a sophisticated deep
                // teal anchored on the spec's own `#167D78` (set here as
                // 600, matching how every `.btn-primary`/active-nav usage
                // in this codebase already expects the "main" tone to sit
                // at 600 with 700 as its hover/darker shade — the exact
                // slot `#D4A000` occupied in the old gold scale). 700–900
                // are pulled darker for text/icon contrast on white; 50–300
                // are light tints for badges, hover fills and icon chips.
                accent: {
                    50: '#EAF6F5',
                    100: '#D1EBE9',
                    200: '#A3D7D3',
                    300: '#74C2BC',
                    400: '#3CA39C',
                    500: '#1F8D86',
                    600: '#167D78',
                    700: '#126863',
                    800: '#0E514D',
                    900: '#0A3A37',
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
            // Premium-SaaS redesign pass: the Fraunces serif display font
            // (used only by .page-title) is dropped in favor of Inter
            // everywhere — a warm editorial serif heading doesn't fit the
            // flatter, Linear/Notion/Stripe-inspired look this pass is
            // after, which leans on weight and spacing for hierarchy
            // instead of a second typeface. Manrope is kept as the first
            // fallback (so anything still mid-load briefly renders close
            // to its final look) with the same system-font chain after it.
            fontFamily: {
                sans: ['Inter', 'Manrope', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            borderRadius: {
                sm: '6px',
                DEFAULT: '8px',
                md: '8px',
                lg: '12px',
            },
            // `soft` (the ERES-demo-inspired floating shadow from UI/UX
            // round 2) is kept defined — a couple of one-off decorative
            // spots may still reference it — but .card/.panel/.btn-* no
            // longer @apply it by default. The premium-SaaS spec explicitly
            // calls for subtle borders over heavy shadows, so separation
            // now comes from a 1px border-ink-200 instead (resources/css/
            // app.css), matching the Linear/Notion/Stripe reference point.
            boxShadow: {
                soft: '0 12px 33px 0 rgba(62, 73, 84, 0.08)',
            },
        },
    },
    plugins: [require('@tailwindcss/forms')],
};
