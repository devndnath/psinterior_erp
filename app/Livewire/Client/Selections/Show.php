<?php

namespace App\Livewire\Client\Selections;

use App\Models\SelectionBoard;
use App\Services\SelectionBoardService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md. Calls SelectionBoardService::recordDecision()
 * directly once ownership is confirmed, the same method staff use on the
 * client's behalf from the staff Show page — never through
 * SelectionBoardPolicy.
 */
#[Layout('layouts.client-portal')]
class Show extends Component
{
    public SelectionBoard $board;

    public ?int $chosenOptionId = null;

    public string $comment = '';

    public function mount(SelectionBoard $board): void
    {
        $client = Auth::guard('client')->user()->client;
        abort_unless($client, 403, 'No client record linked to this account.');
        abort_unless($board->project && $board->project->client_id === $client->id, 403);
        abort_if($board->status->value === 'draft', 404);

        $this->board = $board;
    }

    public function chooseOption(int $optionId): void
    {
        $this->chosenOptionId = $optionId;
        $this->comment = '';
    }

    public function cancelChoice(): void
    {
        $this->chosenOptionId = null;
        $this->comment = '';
    }

    public function confirmChoice(SelectionBoardService $service): void
    {
        $this->validate([
            'chosenOptionId' => ['required', 'integer'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $option = $this->board->options()->whereKey($this->chosenOptionId)->firstOrFail();
        $service->recordDecision($this->board, $option, $this->comment ?: null);

        $this->board = $this->board->fresh();
        $this->reset(['chosenOptionId', 'comment']);

        $this->dispatch('toast', message: 'Your choice has been recorded.', type: 'success');
    }

    #[Title('Selection')]
    public function render()
    {
        $this->board->load(['project', 'options.product', 'options.file', 'decidedOption']);

        return view('livewire.client.selections.show');
    }
}
