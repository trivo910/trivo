<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;

class ServiceSeoRevisionRequest extends PageSeoRevisionRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');

        return $service instanceof Service && ($this->user()?->can('updateSeo', $service) ?? false);
    }
}
