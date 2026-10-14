/**
 * Day/night auto theme (tailwind.config.js: darkMode: 'class').
 *
 * Until the person picks a theme explicitly (clicking <x-theme-toggle>
 * in the topbar / portal headers, which stores an override under the
 * 'theme' localStorage key), the UI follows the actual local clock —
 * dark from 6pm to 6am, light the rest of the day — rather than
 * freezing on whatever it happened to load with. The identical
 * calculation also runs inline in each layout's <head> (admin,
 * client-portal, vendor-portal) so the very first paint already has the
 * right theme with no flash; this module's job is to keep re-applying
 * it while the tab stays open, so a tab left open across the 6am/6pm
 * boundary actually switches live instead of waiting for a reload.
 *
 * A stored override always wins here — this never fights a choice the
 * person made deliberately.
 */
function isNightNow() {
    const hour = new Date().getHours();
    return hour < 6 || hour >= 18;
}

function applyAutoTheme() {
    let stored = null;
    try {
        stored = localStorage.getItem('theme');
    } catch (e) {
        // Private browsing / disabled storage — nothing to defer to,
        // fall through to the time-of-day default below.
    }

    if (stored) {
        return;
    }

    const dark = isNightNow();
    if (document.documentElement.classList.contains('dark') !== dark) {
        document.documentElement.classList.toggle('dark', dark);
        // Lets <x-theme-toggle>'s own icon state (set once at Alpine init)
        // re-sync when the auto-switch happens while it's already on screen.
        window.dispatchEvent(new CustomEvent('theme-changed'));
    }
}

applyAutoTheme();
// Checked every minute — cheap, and frequent enough that the 6am/6pm
// crossover is never more than a minute late for a tab left open overnight.
setInterval(applyAutoTheme, 60 * 1000);
