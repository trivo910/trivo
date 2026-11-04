<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use App\Services\Content\ContentDrafts;

class ServiceDraftRequest extends PageDraftRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');

        return $service instanceof Service ? ($this->user()?->can('update', $service) ?? false)
            : ($this->user()?->can('create', Service::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_replace(parent::rules(), ['content_version' => [$this->route('service') ? 'required' : 'nullable', 'integer', 'min:1'],
            'sort' => ['required', 'integer', 'between:0,10000'], 'media_id' => ['nullable', 'integer', 'min:1']]);
    }

    protected function passedValidation(): void
    {
        app(ContentDrafts::class)->validate($this->validated(), $this->route('service') instanceof Service ? $this->route('service') : new Service);
    }
}
