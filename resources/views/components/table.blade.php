{{--
    Consistent table chrome wrapper: a .panel-style container plus the
    .table-base header/zebra/hover/padding classes (app.css), while the
    calling view keeps full control of its own <thead>/<tbody> columns —
    same division of responsibility as the hand-rolled tables it replaces
    (e.g. resources/views/livewire/leads/index.blade.php,
    resources/views/livewire/invoices/index.blade.php).

    <x-table>
        <thead>
            <tr>
                <th class="th-sortable" wire:click="sortBy('name')">Name</th>
                ...
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row) ... @endforeach
        </tbody>
    </x-table>
--}}
@props(['empty' => false])

<div {{ $attributes->merge(['class' => 'panel overflow-hidden']) }}>
    @if ($empty)
        {{ $slot }}
    @else
        <div class="overflow-x-auto">
            <table class="table-base">
                {{ $slot }}
            </table>
        </div>
    @endif
</div>
