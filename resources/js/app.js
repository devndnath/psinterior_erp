import './theme';

// Alpine ships inside Livewire 3 already (window.Alpine), so it is not
// imported/started separately here — starting it twice breaks Livewire's
// own directives. Register any custom Alpine components via
// document.addEventListener('alpine:init', () => Alpine.data(...)) in a
// dedicated file under resources/js/ as modules are added, and import it
// here.

// Chart.js (~200 KB) is only needed by the pages that draw charts (the
// dashboard and the CRM overview), so it is split into its own file and
// fetched the first time a chart asks for it, not on every page:
//     const Chart = await window.loadChart();  new Chart(canvas, {...});
let chartLoader;
window.loadChart = () => (chartLoader ??= import('chart.js/auto').then((m) => (window.Chart = m.default)));

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
        expenseFill: dark ? 'rgba(189, 138, 0, 0.16)' : 'rgba(198, 143, 0, 0.12)', // area under the expense line
        tooltipBg: dark ? '#F5F5F0' : '#20241F', // brand charcoal pill (inverted in dark)
        tooltipText: dark ? '#20241F' : '#FFFFFF',
        single: dark ? '#BD8A00' : '#C68F00', // single-series charts
        text: css('--color-ink-500'),
        grid: css('--color-ink-100'),
        baseline: css('--color-ink-200'),
        surface: css('--color-surface'),
    };
};
