<?php

namespace App\Livewire\Boqs;

use App\Models\Boq;
use App\Services\BoqService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Boq $boq;

    public function mount(Boq $boq): void
    {
        Gate::authorize('view', $boq);

        $this->boq = $boq;
    }

    public function activate(BoqService $service): void
    {
        Gate::authorize('update', $this->boq);

        $this->boq = $service->activate($this->boq);

        $this->dispatch('toast', message: "BOQ {$this->boq->boq_no} is now Active.", type: 'success');
    }

    /**
     * Starts a new version by copying this BOQ's current items into a
     * fresh Draft BOQ for the same project — the usual starting point
     * for a re-estimate (BoqService::create, ARCHITECTURE.md §3.6).
     */
    public function createNewVersion(BoqService $service): void
    {
        Gate::authorize('create', Boq::class);

        $items = $this->boq->items->map(fn ($item) => [
            'item' => $item->item,
            'description' => $item->description,
            'category' => $item->category,
            'qty' => $item->qty,
            'unit' => $item->unit,
            'rate' => $item->rate,
            'labour_cost' => $item->labour_cost,
            'material_cost' => $item->material_cost,
            'remarks' => $item->remarks,
        ])->all();

        $newBoq = $service->create($this->boq->project, [
            'discount' => $this->boq->discount,
            'tax' => $this->boq->tax,
        ], $items);

        $this->dispatch('toast', message: "New BOQ version {$newBoq->boq_no} created.", type: 'success');
        $this->redirectRoute('boqs.edit', ['boq' => $newBoq->id], navigate: true);
    }

    public function deleteBoq(): void
    {
        Gate::authorize('delete', $this->boq);

        $boqNo = $this->boq->boq_no;
        $this->boq->delete();

        $this->dispatch('toast', message: "BOQ {$boqNo} deleted.", type: 'success');
        $this->redirectRoute('boqs.index', navigate: true);
    }

    #[Title('BOQ')]
    public function render()
    {
        $this->boq->load(['project.client', 'items']);

        return view('livewire.boqs.show');
    }
}
