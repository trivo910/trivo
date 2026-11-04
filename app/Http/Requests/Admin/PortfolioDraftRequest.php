<?php

namespace App\Http\Requests\Admin;

use App\Models\PortfolioItem;
use App\Services\Content\ContentDrafts;

class PortfolioDraftRequest extends ServiceDraftRequest
{
    public function authorize(): bool
    {
        $portfolio = $this->route('portfolio');

        return $portfolio instanceof PortfolioItem ? ($this->user()?->can('update', $portfolio) ?? false)
            : ($this->user()?->can('create', PortfolioItem::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_replace(parent::rules(), ['content_version' => [$this->route('portfolio') ? 'required' : 'nullable', 'integer', 'min:1']]);
    }

    protected function passedValidation(): void
    {
        app(ContentDrafts::class)->validate($this->validated(), $this->route('portfolio') instanceof PortfolioItem ? $this->route('portfolio') : new PortfolioItem);
    }
}
