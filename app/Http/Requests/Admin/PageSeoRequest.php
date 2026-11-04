<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use App\Services\Content\PageSeo;
use Illuminate\Foundation\Http\FormRequest;

class PageSeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $page = $this->route('page');

        return $page instanceof Page && ($this->user()?->can('updateSeo', $page) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = ['content_version' => ['required', 'integer', 'min:1'], 'sitemap_include' => ['required', 'boolean']];
        foreach (PageSeo::FIELDS as $field) {
            $rules[$field] ??= ['nullable'];
        }

        return $rules;
    }

    protected function passedValidation(): void
    {
        app(PageSeo::class)->validate($this->validated());
    }
}
