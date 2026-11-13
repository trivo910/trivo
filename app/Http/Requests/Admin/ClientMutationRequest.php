<?php

namespace App\Http\Requests\Admin;

use App\Models\Client;

class ClientMutationRequest extends ServiceMutationRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');
        $ability = match ($this->route()?->getName()) {
            'admin.clients.restore' => 'restore', 'admin.clients.revision' => 'update', default => 'delete',
        };

        return $client instanceof Client && ($this->user()?->can($ability, $client) ?? false);
    }
}
