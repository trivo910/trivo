<?php

namespace App\Http\Requests\Admin;

use App\Models\Testimonial;
use App\Services\Content\ContentDrafts;

class TestimonialDraftRequest extends ServiceDraftRequest
{
    public function authorize(): bool
    {
        $testimonial = $this->route('testimonial');

        return $testimonial instanceof Testimonial ? ($this->user()?->can('update', $testimonial) ?? false)
            : ($this->user()?->can('create', Testimonial::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_replace(parent::rules(), ['path' => ['nullable', 'string', 'max:768'], 'content_version' => [$this->route('testimonial') ? 'required' : 'nullable', 'integer', 'min:1']]);
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
        app(ContentDrafts::class)->validate($this->validated(), $this->route('testimonial') instanceof Testimonial ? $this->route('testimonial') : new Testimonial);
    }
}
