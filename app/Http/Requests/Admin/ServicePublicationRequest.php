<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;

class ServicePublicationRequest extends PagePublicationRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');

        return $service instanceof Service && ($this->user()?->can($this->input('operation') === 'cancel' ? 'cancelPublication' : 'publish', $service) ?? false);
    }
}
