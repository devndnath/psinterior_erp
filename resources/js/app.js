import './bootstrap';
import './theme';

// Alpine ships inside Livewire 3 already (window.Alpine), so it is not
// imported/started separately here — starting it twice breaks Livewire's
// own directives. Register any custom Alpine components via
// document.addEventListener('alpine:init', () => Alpine.data(...)) in a
// dedicated file under resources/js/ as modules are added, and import it
// here.

// Phase 13: Chart.js was installed (package.json) but never imported or
// used anywhere — wiring it up here, minimally, so a future Dashboard/
// Reports redesign phase can do `new Chart(ctx, {...})` from an inline
// <script> or an Alpine component without any further build-step work.
// No chart is added to any page in this phase — this is wiring only.
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Brand-layout pass: one place for every dashboard/report chart's colors,
// read at chart-init time so each chart picks the current theme. Series
// steps were validated as a pair (dataviz validator: lightness band,
// chroma, CVD and normal-vision separation) separately for each surface —
// light (#FFFFFF) and dark (#1B1D19) — so the dark values are their own
// selected steps, not an automatic flip. Text/grid colors come from the
// app's own ink tokens (resources/css/app.css) so axes match the UI.
window.psChartTheme = function () {
    const root = document.documentElement;
    const dark = root.classList.contains('dark');
    const css = (name) => 'rgb(' + getComputedStyle(root).getPropertyValue(name).trim().split(/\s+/).join(',') + ')';

    return {
        dark,
        income: dark ? '#12A372' : '#059669', // brand emerald
        expense: dark ? '#BD8A00' : '#C68F00', // brand gold, chart step
        single: dark ? '#BD8A00' : '#C68F00', // single-series charts
        text: css('--color-ink-500'),
        grid: css('--color-ink-100'),
        baseline: css('--color-ink-200'),
        surface: css('--color-surface'),
    };
};
