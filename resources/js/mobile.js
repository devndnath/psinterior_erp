/**
 * Mobile app shell helpers.
 *
 * 1. Card lists: on phones each <x-table> row is drawn as a card
 *    (app.css .table-cards), and every cell shows its column heading as a
 *    label. The labels are copied here from the table's <thead> into a
 *    data-label attribute — once on load and again whenever Livewire
 *    redraws a list (filtering, paging, sorting), via a MutationObserver.
 * 2. Installable app: registers the service worker (public/sw.js), which
 *    caches the built CSS/JS and shows an offline page when there is no
 *    connection.
 */
function labelTables(root = document) {
    root.querySelectorAll('table.table-cards').forEach((table) => {
        const heads = [...table.querySelectorAll(':scope > thead th')].map((th) => th.textContent.replace(/\s+/g, ' ').trim());
        table.querySelectorAll(':scope > tbody > tr').forEach((tr) => {
            let col = 0;
            for (const td of tr.children) {
                const span = Number(td.getAttribute('colspan') || 1);
                const label = span > 1 ? '' : (heads[col] ?? '');
                if (td.getAttribute('data-label') !== label) td.setAttribute('data-label', label);
                col += span;
            }
        });
    });
}

let queued = false;
function queueLabel() {
    if (queued) return;
    queued = true;
    requestAnimationFrame(() => {
        queued = false;
        labelTables();
    });
}

let observer;
function start() {
    labelTables();
    observer?.disconnect();
    observer = new MutationObserver((mutations) => {
        // Only react to rows/cells being added — our own attribute writes don't retrigger.
        if (mutations.some((m) => m.type === 'childList')) queueLabel();
    });
    observer.observe(document.body, { childList: true, subtree: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
} else {
    start();
}
// wire:navigate swaps <body>, so watch the new one.
document.addEventListener('livewire:navigated', start);

if ('serviceWorker' in navigator && window.isSecureContext) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
