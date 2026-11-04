<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use App\Services\Content\ContentSeo;

class ServiceSeoRequest extends PageSeoRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');

        return $service instanceof Service && ($this->user()?->can('updateSeo', $service) ?? false);
    }

    protected function passedValidation(): void
    {
        app(ContentSeo::class)->validate($this->validated(), 'service');
    }
}
