<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use App\Services\Content\ContentDrafts;
use Illuminate\Foundation\Http\FormRequest;

class PageDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $page = $this->route('page');

        return $page instanceof Page ? ($this->user()?->can('update', $page) ?? false)
            : ($this->user()?->can('create', Page::class) ?? false);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('blocks_json')) {
            $json = $this->input('blocks_json');
            $decoded = is_string($json) && strlen($json) <= 200000 ? json_decode($json, true) : null;
            $this->merge(['blocks' => is_array($decoded) && array_is_list($decoded) ? $decoded : 'invalid']);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'path' => ['required', 'string', 'max:768'],
            'blocks' => ['present', 'array', 'max:50'], 'blocks_json' => ['nullable', 'string', 'max:200000'],
            'content_version' => [$this->route('page') ? 'required' : 'nullable', 'integer', 'min:1']];
    }

    protected function passedValidation(): void
    {
        app(ContentDrafts::class)->validate($this->validated(), $this->route('page') instanceof Page ? $this->route('page') : null);
    }
}
