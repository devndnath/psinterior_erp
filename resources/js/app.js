import './bootstrap';

// Alpine ships inside Livewire 3 already (window.Alpine), so it is not
// imported/started separately here — starting it twice breaks Livewire's
// own directives. Register any custom Alpine components via
// document.addEventListener('alpine:init', () => Alpine.data(...)) in a
// dedicated file under resources/js/ as modules are added, and import it
// here.
