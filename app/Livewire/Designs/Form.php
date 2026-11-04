<?php

namespace App\Livewire\Designs;

use App\Enums\DesignCategory;
use App\Enums\DesignStatus;
use App\Models\Client;
use App\Models\Design;
use App\Models\Project;
use App\Services\DesignService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Creates a design together with its first revision in one step — a
 * design with no uploaded file isn't meaningful, so the file field is
 * required here. Every later revision is added from the Design Show page
 * instead (DesignService::addRevision), never through this form, since
 * revisions are append-only.
 *
 * A design is either for a project, or sold on its own (`for = sold`):
 * then it has no project, the client is picked directly, and its price
 * is what the Design Sale invoice for it will bill.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    use WithFileUploads;

    public ?Design $design = null;

    /** 'project' = a project's design; 'sold' = a design sold on its own, no project. */
    public string $for = 'project';
    public string $project_id = '';
    public string $client_id = '';
    public string $price = '';
    public string $title = '';
    public string $category = '';
    public string $description = '';
    public $file = null;

    public function mount(?Design $design = null): void
    {
        $this->design = $design?->exists ? $design : null;

        Gate::authorize($this->design ? 'update' : 'create', $this->design ?? Design::class);

        if ($this->design) {
            $this->fill([
                'for' => $this->design->isSold() ? 'sold' : 'project',
                'client_id' => (string) $this->design->client_id,
                'price' => $this->design->price !== null ? (string) $this->design->price : '',
                'project_id' => (string) $this->design->project_id,
                'title' => $this->design->title,
                'category' => $this->design->category?->value ?? '',
                'description' => (string) $this->design->description,
            ]);

            return;
        }

        $projectId = request()->query('project');

        if ($projectId && Project::whereKey($projectId)->exists()) {
            $this->project_id = (string) $projectId;
        } elseif (request()->query('for') === 'sold') {
            $this->for = 'sold';
            $clientId = request()->query('client');
            if ($clientId && Client::whereKey($clientId)->exists()) {
                $this->client_id = (string) $clientId;
            }
        }
    }

    protected function rules(): array
    {
        $rules = [
            'for' => ['required', 'in:project,sold'],
            'project_id' => ['required_if:for,project', 'nullable', 'exists:projects,id'],
            'client_id' => ['required_if:for,sold', 'nullable', 'exists:clients,id'],
            // Required for a sold design — it's what gets invoiced.
            'price' => ['required_if:for,sold', 'nullable', 'numeric', 'min:0'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'in:'.implode(',', array_column(DesignCategory::cases(), 'value'))],
            'description' => ['nullable', 'string', 'max:2000'],
        ];

        if (! $this->design) {
            $rules['file'] = ['required', 'file', 'max:51200'];
        }

        return $rules;
    }

    public function save(DesignService $service): void
    {
        $data = $this->validate();
        $sold = $data['for'] === 'sold';
        $owner = [
            'project_id' => $sold ? null : $data['project_id'],
            'client_id' => $sold ? $data['client_id'] : null,
            'price' => $data['price'] === '' || $data['price'] === null ? null : $data['price'],
        ];

        if ($this->design) {
            $service->update($this->design, $owner + [
                'title' => $data['title'],
                'category' => $data['category'] ?: null,
                'description' => $data['description'] ?: null,
            ]);

            $this->dispatch('toast', message: 'Design updated.', type: 'success');
            $this->redirectRoute('designs.show', ['design' => $this->design->id], navigate: true);

            return;
        }

        $design = $service->create($owner + [
            'title' => $data['title'],
            'category' => $data['category'] ?: null,
            'description' => $data['description'] ?: null,
            'status' => DesignStatus::Draft->value,
            'uploaded_by' => Auth::id(),
        ], $this->file, Auth::user());

        $this->dispatch('toast', message: "Design {$design->design_no} created.", type: 'success');
        $this->redirectRoute('designs.show', ['design' => $design->id], navigate: true);
    }

    #[Title('Design')]
    public function render()
    {
        return view('livewire.designs.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'clients' => Client::orderBy('name')->get(['id', 'name', 'client_no']),
            'categories' => DesignCategory::options(),
        ]);
    }
}
