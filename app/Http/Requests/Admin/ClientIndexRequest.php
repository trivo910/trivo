<?php

namespace App\Http\Requests\Admin;

use App\Models\Client;

class ClientIndexRequest extends PageIndexRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Client::class) ?? false;
    }
}
