{{--
    Light/dark mode toggle. Flips a `dark` class on <html> (tailwind.config
    .js: darkMode: 'class') and remembers the choice in localStorage under
    the same 'theme' key the blocking inline script in each layout's <head>
    reads on first paint (see layouts/admin.blade.php, client-portal.blade
    .php, vendor-portal.blade.php) — that script is what prevents a flash
    of the wrong theme before Alpine initializes here. Until this is
    clicked, the theme instead follows the real time of day (resources/js/
    theme.js), which dispatches a 'theme-changed' window event whenever it
    auto-switches — listened for below so this button's own sun/moon icon
    stays in sync if an auto-switch happens while it's already on screen.

    Self-contained (its own x-data), so it can be dropped into any layout's
    header without wiring anything else up.

    <x-theme-toggle />
--}}
<button
    type="button"
    x-data="{
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = ! this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            try {
                localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            } catch (e) {
                // Private browsing / disabled storage — the toggle still
                // works for this page view, it just won't persist.
            }
        },
    }"
    x-on:theme-changed.window="dark = document.documentElement.classList.contains('dark')"
    @click="toggle()"
    {{ $attributes->merge(['class' => 'rounded-lg p-2 text-ink-500 transition hover:bg-ink-50 hover:text-ink-900']) }}
    :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'"
    :title="dark ? 'Switch to light mode' : 'Switch to dark mode'"
>
    <x-icon name="sun" class="h-5 w-5" x-show="dark" x-cloak />
    <x-icon name="moon" class="h-5 w-5" x-show="!dark" x-cloak />
</button>
