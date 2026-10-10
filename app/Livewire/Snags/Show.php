<?php

namespace App\Livewire\Snags;

use App\Models\Snag;
use App\Models\User;
use App\Services\SnagService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Snag $snag;

    public string $assign_user_id = '';

    public function mount(Snag $snag): void
    {
        Gate::authorize('view', $snag);

        $this->snag = $snag;
        $this->assign_user_id = (string) $snag->assigned_to;
    }

    public function assign(SnagService $service): void
    {
        Gate::authorize('assign', $this->snag);

        $this->validate(['assign_user_id' => ['required', 'exists:users,id']]);

        try {
            $this->snag = $service->assign($this->snag, (int) $this->assign_user_id);
            $this->dispatch('toast', message: "Snag {$this->snag->snag_no} assigned.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function startProgress(SnagService $service): void
    {
        Gate::authorize('startProgress', $this->snag);

        try {
            $this->snag = $service->startProgress($this->snag);
            $this->dispatch('toast', message: "Snag {$this->snag->snag_no} is now in progress.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function markFixed(SnagService $service): void
    {
        Gate::authorize('markFixed', $this->snag);

        try {
            $this->snag = $service->markFixed($this->snag);
            $this->dispatch('toast', message: "Snag {$this->snag->snag_no} marked fixed.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function verify(SnagService $service): void
    {
        Gate::authorize('verify', $this->snag);

        try {
            $this->snag = $service->verify($this->snag);
            $this->dispatch('toast', message: "Snag {$this->snag->snag_no} verified.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function close(SnagService $service): void
    {
        Gate::authorize('close', $this->snag);

        try {
            $this->snag = $service->close($this->snag);
            $this->dispatch('toast', message: "Snag {$this->snag->snag_no} closed.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function reopen(SnagService $service): void
    {
        Gate::authorize('reopen', $this->snag);

        try {
            $this->snag = $service->reopen($this->snag);
            $this->dispatch('toast', message: "Snag {$this->snag->snag_no} reopened.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function deleteSnag(): void
    {
        Gate::authorize('delete', $this->snag);

        $no = $this->snag->snag_no;
        $this->snag->delete();

        $this->dispatch('toast', message: "Snag {$no} deleted.", type: 'success');
        $this->redirectRoute('snags.index', navigate: true);
    }

    #[Title('Snag')]
    public function render()
    {
        $this->snag->load(['project', 'inspection', 'assignedTo']);

        return view('livewire.snags.show', [
            'users' => User::staff()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
