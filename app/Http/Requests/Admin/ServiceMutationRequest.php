<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class ServiceMutationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');
        $ability = match ($this->route()?->getName()) {
            'admin.services.restore' => 'restore', 'admin.services.revision' => 'update', default => 'delete',
        };

        return $service instanceof Service && ($this->user()?->can($ability, $service) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['content_version' => ['required', 'integer', 'min:1']];
    }
}
