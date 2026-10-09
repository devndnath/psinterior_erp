{{-- Generic toast notifications. Any Livewire component calls:
     $this->dispatch('toast', message: 'Saved.', type: 'success');
     type is one of: success | error | info (defaults to 'info'). --}}
<div
    x-data="{
        toasts: [],
        add(e) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message: e.message, type: e.type ?? 'info' });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },
    }"
    x-on:toast.window="add($event.detail)"
    class="pointer-events-none fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-2 sm:items-end sm:px-6"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto w-full max-w-sm rounded-lg p-4 shadow-lg ring-1 ring-black/5"
            :class="{
                'bg-green-50 text-green-800': toast.type === 'success',
                'bg-red-50 text-red-800': toast.type === 'error',
                'bg-blue-50 text-blue-800': toast.type === 'info',
            }"
        >
            <div class="flex items-start gap-3">
                <p class="flex-1 text-sm font-medium" x-text="toast.message"></p>
                <button @click="remove(toast.id)" class="shrink-0 opacity-60 hover:opacity-100">&times;</button>
            </div>
        </div>
    </template>
</div>
