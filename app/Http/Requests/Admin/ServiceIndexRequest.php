<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;

class ServiceIndexRequest extends PageIndexRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Service::class) ?? false;
    }
}
