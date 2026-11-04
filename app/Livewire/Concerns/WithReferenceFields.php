<?php

namespace App\Livewire\Concerns;

use App\Models\Client;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * The "Reference — who introduced them?" fields shared by the Lead and
 * Client forms (see App\Models\Concerns\HasReference). The form calls
 * fillReference() in mount(), merges referenceRules() into its rules()
 * and passes its validated data through referenceData() before saving.
 */
trait WithReferenceFields
{
    public string $reference_type = '';
    public string $reference_client_id = '';
    public string $reference_employee_id = '';
    public string $reference_name = '';
    public string $reference_phone = '';

    protected function fillReference(Model $model): void
    {
        $this->reference_type = (string) $model->reference_type;
        $this->reference_client_id = (string) $model->reference_client_id;
        $this->reference_employee_id = (string) $model->reference_employee_id;
        $this->reference_name = (string) $model->reference_name;
        $this->reference_phone = (string) $model->reference_phone;
    }

    protected function referenceRules(?int $exceptClientId = null): array
    {
        // A client can't be recorded as their own referrer.
        $clientExists = Rule::exists('clients', 'id');
        if ($exceptClientId) {
            $clientExists->whereNot('id', $exceptClientId);
        }

        return [
            'reference_type' => ['nullable', 'in:client,employee,other'],
            'reference_client_id' => ['required_if:reference_type,client', 'nullable', $clientExists],
            'reference_employee_id' => ['required_if:reference_type,employee', 'nullable', 'exists:employees,id'],
            'reference_name' => ['required_if:reference_type,other', 'nullable', 'string', 'max:255'],
            'reference_phone' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function referenceMessages(): array
    {
        return [
            'reference_client_id.required_if' => 'Choose the client who referred them.',
            'reference_employee_id.required_if' => 'Choose the staff member who referred them.',
            'reference_name.required_if' => "Enter the referrer's name.",
            'reference_client_id.exists' => 'Choose a different client.',
        ];
    }

    /** Keeps only the columns that belong to the chosen kind of referrer. */
    protected function referenceData(array $data): array
    {
        $type = $data['reference_type'] ?? '' ?: null;

        return array_merge($data, [
            'reference_type' => $type,
            'reference_client_id' => $type === 'client' ? ($data['reference_client_id'] ?: null) : null,
            'reference_employee_id' => $type === 'employee' ? ($data['reference_employee_id'] ?: null) : null,
            'reference_name' => $type === 'other' ? ($data['reference_name'] ?: null) : null,
            'reference_phone' => $type === 'other' ? (($data['reference_phone'] ?? '') ?: null) : null,
        ]);
    }

    protected function referenceOptions(?int $exceptClientId = null): array
    {
        return [
            'referenceClients' => Client::query()->when($exceptClientId, fn ($q) => $q->whereKeyNot($exceptClientId))
                ->orderBy('name')->get(['id', 'name', 'client_no', 'phone']),
            'referenceEmployees' => Employee::query()->orderBy('name')->get(['id', 'name', 'employee_id']),
        ];
    }
}
