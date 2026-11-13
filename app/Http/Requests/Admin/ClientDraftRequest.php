<?php

namespace App\Http\Requests\Admin;

use App\Models\Client;
use App\Services\Content\ContentDrafts;

class ClientDraftRequest extends ServiceDraftRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');

        return $client instanceof Client ? ($this->user()?->can('update', $client) ?? false)
            : ($this->user()?->can('create', Client::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_replace(parent::rules(), ['path' => ['nullable', 'string', 'max:768'], 'content_version' => [$this->route('client') ? 'required' : 'nullable', 'integer', 'min:1']]);
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        if (! $this->filled('path')) {
            $this->merge(['path' => null]);
        }
    }

    protected function passedValidation(): void
    {
        app(ContentDrafts::class)->validate($this->validated(), $this->route('client') instanceof Client ? $this->route('client') : new Client);
    }
}
