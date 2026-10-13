<?php

namespace App\Livewire\MaterialSelections;

use App\Models\Product;
use App\Models\SelectionBoard;
use App\Models\SelectionOption;
use App\Services\SelectionBoardService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Show extends Component
{
    use WithFileUploads;

    public SelectionBoard $board;

    public bool $showOptionForm = false;

    public string $optionLabel = '';

    public string $optionProductId = '';

    public string $optionPrice = '';

    public string $optionNotes = '';

    public $optionImage = null;

    public ?int $decidingOptionId = null;

    public string $decisionComment = '';

    public function mount(SelectionBoard $board): void
    {
        Gate::authorize('view', $board);

        $this->board = $board;
    }

    public function deleteBoard(): void
    {
        Gate::authorize('delete', $this->board);

        $boardNo = $this->board->board_no;
        $this->board->delete();

        $this->dispatch('toast', message: "Selection {$boardNo} deleted.", type: 'success');
        $this->redirectRoute('material-selections.index', navigate: true);
    }

    public function toggleOptionForm(): void
    {
        Gate::authorize('manageOptions', $this->board);

        $this->showOptionForm = ! $this->showOptionForm;
        $this->reset(['optionLabel', 'optionProductId', 'optionPrice', 'optionNotes', 'optionImage']);
    }

    public function addOption(SelectionBoardService $service): void
    {
        Gate::authorize('manageOptions', $this->board);

        $this->validate([
            'optionLabel' => ['required', 'string', 'max:150'],
            'optionProductId' => ['nullable', 'exists:products,id'],
            'optionPrice' => ['nullable', 'numeric', 'min:0'],
            'optionNotes' => ['nullable', 'string', 'max:1000'],
            'optionImage' => ['nullable', 'image', 'max:10240'],
        ]);

        $service->addOption($this->board, [
            'label' => $this->optionLabel,
            'product_id' => $this->optionProductId ?: null,
            'price' => $this->optionPrice !== '' ? $this->optionPrice : null,
            'notes' => $this->optionNotes ?: null,
        ], $this->optionImage);

        $this->board = $this->board->fresh();
        $this->showOptionForm = false;
        $this->reset(['optionLabel', 'optionProductId', 'optionPrice', 'optionNotes', 'optionImage']);

        $this->dispatch('toast', message: 'Option added.', type: 'success');
    }

    public function removeOption(int $optionId, SelectionBoardService $service): void
    {
        Gate::authorize('manageOptions', $this->board);

        $option = $this->board->options()->whereKey($optionId)->firstOrFail();
        $service->removeOption($this->board, $option);

        $this->board = $this->board->fresh();

        $this->dispatch('toast', message: 'Option removed.', type: 'success');
    }

    public function sendToClient(SelectionBoardService $service): void
    {
        Gate::authorize('manageOptions', $this->board);

        $service->send($this->board);
        $this->board = $this->board->fresh();

        $this->dispatch('toast', message: 'Selection sent to the client.', type: 'success');
    }

    public function cancelBoard(SelectionBoardService $service): void
    {
        Gate::authorize('manageOptions', $this->board);

        $service->cancel($this->board);
        $this->board = $this->board->fresh();

        $this->dispatch('toast', message: 'Selection cancelled.', type: 'success');
    }

    public function openDecisionForm(int $optionId): void
    {
        Gate::authorize('recordDecision', $this->board);

        $this->decidingOptionId = $optionId;
        $this->decisionComment = '';
    }

    public function cancelDecisionForm(): void
    {
        $this->reset(['decidingOptionId', 'decisionComment']);
    }

    /**
     * Staff recording the client's pick on their behalf — the same
     * underlying action the client's own portal page can take, gated on
     * the distinct `decide_material_selection` ability (see
     * SelectionBoardPolicy::recordDecision).
     */
    public function recordDecision(SelectionBoardService $service): void
    {
        Gate::authorize('recordDecision', $this->board);

        $this->validate([
            'decidingOptionId' => ['required', 'integer'],
            'decisionComment' => ['nullable', 'string', 'max:1000'],
        ]);

        $option = $this->board->options()->whereKey($this->decidingOptionId)->firstOrFail();
        $service->recordDecision($this->board, $option, $this->decisionComment ?: null);

        $this->board = $this->board->fresh();
        $this->reset(['decidingOptionId', 'decisionComment']);

        $this->dispatch('toast', message: 'Decision recorded.', type: 'success');
    }

    #[Title('Material Selection')]
    public function render()
    {
        $this->board->load(['project', 'options.product', 'options.file', 'decidedOption']);

        return view('livewire.material-selections.show', [
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }
}
