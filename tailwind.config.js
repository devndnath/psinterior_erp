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
                // Brand-layout pass: points at the same CSS variables as
                // `accent` below (PS Solution gold), so legacy `brand-*`
                // usages (portal headers' logo chip, the guest/login layout)
                // stay visually identical to the rest of the app.
                brand: {
                    50: 'rgb(var(--color-accent-50) / <alpha-value>)',
                    100: 'rgb(var(--color-accent-100) / <alpha-value>)',
                    200: 'rgb(var(--color-accent-200) / <alpha-value>)',
                    300: 'rgb(var(--color-accent-300) / <alpha-value>)',
                    400: 'rgb(var(--color-accent-400) / <alpha-value>)',
                    500: 'rgb(var(--color-accent-500) / <alpha-value>)',
                    600: 'rgb(var(--color-accent-600) / <alpha-value>)',
                    700: 'rgb(var(--color-accent-700) / <alpha-value>)',
                    800: 'rgb(var(--color-accent-800) / <alpha-value>)',
                    900: 'rgb(var(--color-accent-900) / <alpha-value>)',
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
                // Brand-layout pass: back to PS Solution's own gold
                // (#F4B400 = 500, the site's CTA/nav-highlight color), now
                // CSS-variable-backed (values in resources/css/app.css) so it
                // re-steps per theme. 500 is the brand fill in both themes;
                // 600/700 are the TEXT/link steps (168 `text-accent-600`
                // usages app-wide) — a deep gold in light mode so links
                // clear WCAG contrast on white, flipping to a bright gold in
                // dark mode so they stay readable on the dark surface.
                accent: {
                    50: 'rgb(var(--color-accent-50) / <alpha-value>)',
                    100: 'rgb(var(--color-accent-100) / <alpha-value>)',
                    200: 'rgb(var(--color-accent-200) / <alpha-value>)',
                    300: 'rgb(var(--color-accent-300) / <alpha-value>)',
                    400: 'rgb(var(--color-accent-400) / <alpha-value>)',
                    500: 'rgb(var(--color-accent-500) / <alpha-value>)',
                    600: 'rgb(var(--color-accent-600) / <alpha-value>)',
                    700: 'rgb(var(--color-accent-700) / <alpha-value>)',
                    800: 'rgb(var(--color-accent-800) / <alpha-value>)',
                    900: 'rgb(var(--color-accent-900) / <alpha-value>)',
                },
                // PS Solution brand charcoal (#20241F, pssolutionbd.com's own
                // heading/text color). Static — used for text that sits ON
                // the gold accent fill (e.g. .btn-primary), which must stay
                // dark in both themes, unlike text-ink-900 which flips.
                charcoal: '#20241F',
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
            // Brand-layout pass: Manrope first — the typeface PS Solution's
            // own site uses — with Inter as the fallback while it loads.
            fontFamily: {
                sans: ['Manrope', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
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
