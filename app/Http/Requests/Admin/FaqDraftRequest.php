<?php

namespace App\Http\Requests\Admin;

use App\Models\Faq;
use App\Services\Content\ContentDrafts;

class FaqDraftRequest extends ServiceDraftRequest
{
    public function authorize(): bool
    {
        $faq = $this->route('faq');

        return $faq instanceof Faq ? ($this->user()?->can('update', $faq) ?? false)
            : ($this->user()?->can('create', Faq::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_replace(parent::rules(), ['path' => ['nullable', 'string', 'max:768'], 'content_version' => [$this->route('faq') ? 'required' : 'nullable', 'integer', 'min:1']]);
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
        app(ContentDrafts::class)->validate($this->validated(), $this->route('faq') instanceof Faq ? $this->route('faq') : new Faq);
    }
}
