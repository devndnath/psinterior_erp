<?php

namespace App\Livewire\Designs;

use App\Enums\ApprovalDecision;
use App\Models\Design;
use App\Services\DesignService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Design detail page: metadata, the append-only revision timeline (each
 * with its own approval history), an "Add Revision" quick-upload action,
 * and a "Record Client Decision" action against the latest revision.
 * Both actions delegate to DesignService so the append-only / status-sync
 * rules (ARCHITECTURE.md §3.5 / spec §13) live in one place.
 */
#[Layout('layouts.admin')]
class Show extends Component
{
    use WithFileUploads;

    public Design $design;

    public bool $showRevisionForm = false;

    public $revisionFile = null;

    public string $revisionDescription = '';

    public ?int $decisionRevisionId = null;

    public string $decision = '';

    public string $decisionComment = '';

    public function mount(Design $design): void
    {
        Gate::authorize('view', $design);

        $this->design = $design;
    }

    public function toggleRevisionForm(): void
    {
        Gate::authorize('uploadRevision', $this->design);

        $this->showRevisionForm = ! $this->showRevisionForm;
        $this->reset(['revisionFile', 'revisionDescription']);
    }

    public function addRevision(DesignService $service): void
    {
        Gate::authorize('uploadRevision', $this->design);

        $this->validate([
            'revisionFile' => ['required', 'file', 'max:51200'],
            'revisionDescription' => ['nullable', 'string', 'max:2000'],
        ]);

        $service->addRevision(
            $this->design,
            $this->revisionFile,
            $this->revisionDescription ?: null,
            auth()->user(),
        );

        $this->design = $this->design->fresh();
        $this->showRevisionForm = false;
        $this->reset(['revisionFile', 'revisionDescription']);

        $this->dispatch('toast', message: 'Revision uploaded.', type: 'success');
    }

    public function openDecisionForm(int $revisionId): void
    {
        Gate::authorize('recordApproval', $this->design);

        $this->decisionRevisionId = $revisionId;
        $this->decision = '';
        $this->decisionComment = '';
    }

    public function cancelDecisionForm(): void
    {
        $this->reset(['decisionRevisionId', 'decision', 'decisionComment']);
    }

    public function recordDecision(DesignService $service): void
    {
        Gate::authorize('recordApproval', $this->design);

        $this->validate([
            'decisionRevisionId' => ['required', 'integer'],
            'decision' => ['required', 'string', 'in:'.implode(',', array_column(ApprovalDecision::cases(), 'value'))],
            'decisionComment' => ['nullable', 'string', 'max:2000'],
        ]);

        $revision = $this->design->revisions()->whereKey($this->decisionRevisionId)->firstOrFail();

        $service->recordClientApproval(
            $revision,
            $this->decision,
            $this->decisionComment ?: null,
            request()->ip(),
        );

        $this->design = $this->design->fresh();
        $this->reset(['decisionRevisionId', 'decision', 'decisionComment']);

        $this->dispatch('toast', message: 'Client decision recorded.', type: 'success');
    }

    public function deleteDesign(): void
    {
        Gate::authorize('delete', $this->design);

        $designNo = $this->design->design_no;
        $this->design->delete();

        $this->dispatch('toast', message: "Design {$designNo} deleted.", type: 'success');
        $this->redirectRoute('designs.index', navigate: true);
    }

    #[Title('Design')]
    public function render()
    {
        $this->design->load([
            'project.client',
            'uploader',
            'revisions.file',
            'revisions.uploader',
            'revisions.approvals.client',
        ]);

        return view('livewire.designs.show', [
            'decisionOptions' => ApprovalDecision::options(),
        ]);
    }
}
