<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientContact;

class ClientService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(array $data): Client
    {
        $data['client_no'] = $this->numbering->next('CLIENT');

        return Client::create($data);
    }

    public function update(Client $client, array $data): Client
    {
        $client->update($data);

        return $client->fresh();
    }

    public function addContact(Client $client, array $data): ClientContact
    {
        if (! empty($data['is_primary'])) {
            $client->contacts()->update(['is_primary' => false]);
        }

        return $client->contacts()->create($data);
    }

    public function removeContact(Client $client, int $contactId): void
    {
        $client->contacts()->where('id', $contactId)->delete();
    }
}
