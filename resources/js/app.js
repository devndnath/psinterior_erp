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
