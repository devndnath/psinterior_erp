<?php

namespace App\Livewire\SiteVisits;

use App\Models\Client;
use App\Models\SiteVisit;
use App\Models\User;
use App\Services\FileService;
use App\Services\SiteVisitService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Create/edit a site visit, with a dynamic list of room/area rows (spec
 * §7: "allow multiple rooms/areas") and optional photo uploads attached
 * once the visit exists (FileService needs a persisted model to attach
 * documents to).
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    use WithFileUploads;

    public ?SiteVisit $siteVisit = null;

    public string $client_id = '';
    public string $site_address = '';
    public string $visit_date = '';
    public string $assigned_to = '';
    public string $purpose = '';
    public string $requirements = '';
    public string $notes = '';

    /** @var array<int, array{area_name:string,length:string,width:string,height:string,notes:string}> */
    public array $rooms = [];

    /** @var \Illuminate\Http\UploadedFile[] */
    public array $photos = [];

    public function mount(?SiteVisit $siteVisit = null): void
    {
        $this->siteVisit = $siteVisit?->exists ? $siteVisit : null;

        Gate::authorize($this->siteVisit ? 'update' : 'create', $this->siteVisit ?? SiteVisit::class);

        if ($this->siteVisit) {
            $this->fill([
                'client_id' => (string) $this->siteVisit->client_id,
                'site_address' => $this->siteVisit->site_address,
                'visit_date' => $this->siteVisit->visit_date?->format('Y-m-d') ?? '',
                'assigned_to' => (string) $this->siteVisit->assigned_to,
                'purpose' => $this->siteVisit->purpose,
                'requirements' => $this->siteVisit->requirements,
                'notes' => $this->siteVisit->notes,
            ]);

            $this->rooms = $this->siteVisit->items->map(fn ($item) => [
                'area_name' => $item->area_name,
                'length' => (string) $item->length,
                'width' => (string) $item->width,
                'height' => (string) $item->height,
                'notes' => (string) $item->notes,
            ])->all();
        }

        if (empty($this->rooms)) {
            $this->addRoom();
        }
    }

    public function addRoom(): void
    {
        $this->rooms[] = ['area_name' => '', 'length' => '', 'width' => '', 'height' => '', 'notes' => ''];
    }

    public function removeRoom(int $index): void
    {
        unset($this->rooms[$index]);
        $this->rooms = array_values($this->rooms);

        if (empty($this->rooms)) {
            $this->addRoom();
        }
    }

    protected function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'site_address' => ['nullable', 'string', 'max:500'],
            'visit_date' => ['required', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'requirements' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'rooms.*.area_name' => ['required', 'string', 'max:255'],
            'rooms.*.length' => ['nullable', 'numeric', 'min:0'],
            'rooms.*.width' => ['nullable', 'numeric', 'min:0'],
            'rooms.*.height' => ['nullable', 'numeric', 'min:0'],
            'rooms.*.notes' => ['nullable', 'string', 'max:500'],
            'photos.*' => ['nullable', 'image', 'max:10240'],
        ];
    }

    public function save(SiteVisitService $service, FileService $files): void
    {
        $data = $this->validate();

        $visitData = [
            'client_id' => $data['client_id'],
            'site_address' => $data['site_address'] ?: null,
            'visit_date' => $data['visit_date'],
            'assigned_to' => $data['assigned_to'] !== '' ? $data['assigned_to'] : null,
            'purpose' => $data['purpose'] ?: null,
            'requirements' => $data['requirements'] ?: null,
            'notes' => $data['notes'] ?: null,
        ];

        $rooms = array_map(fn ($room) => [
            'area_name' => $room['area_name'],
            'length' => $room['length'] !== '' ? $room['length'] : null,
            'width' => $room['width'] !== '' ? $room['width'] : null,
            'height' => $room['height'] !== '' ? $room['height'] : null,
            'notes' => $room['notes'] ?: null,
        ], $data['rooms']);

        if ($this->siteVisit) {
            $visit = $service->update($this->siteVisit, $visitData, $rooms);
            $message = "Site visit {$visit->sv_no} updated.";
        } else {
            $visit = $service->create($visitData, $rooms);
            $message = "Site visit {$visit->sv_no} created.";
        }

        foreach ($this->photos as $photo) {
            $files->store($photo, $visit, 'site_photo');
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('site-visits.show', ['siteVisit' => $visit->id], navigate: true);
    }

    #[Title('Site Visit')]
    public function render()
    {
        return view('livewire.site-visits.form', [
            'clients' => Client::orderBy('name')->get(['id', 'name', 'client_no']),
            'assignees' => User::staff()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
