<?php

namespace App\Http\Requests\Admin;

use App\Models\PortfolioItem;
use App\Services\Content\ContentDrafts;
use App\Services\Content\PortfolioLayout;

class PortfolioDraftRequest extends ServiceDraftRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        if ($this->has('case_study_json')) {
            $json = $this->input('case_study_json');
            $decoded = is_string($json) && strlen($json) <= 500000 ? json_decode($json, true) : null;
            $this->merge(['case_study' => is_array($decoded) ? $decoded : 'invalid']);
        }
    }
    public function authorize(): bool
    {
        $portfolio = $this->route('portfolio');

        return $portfolio instanceof PortfolioItem ? ($this->user()?->can('update', $portfolio) ?? false)
            : ($this->user()?->can('create', PortfolioItem::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_replace(parent::rules(), ['content_version' => [$this->route('portfolio') ? 'required' : 'nullable', 'integer', 'min:1'],
            'case_study' => ['sometimes', 'array'], 'case_study_json' => ['sometimes', 'string', 'max:500000']]);
    }

    protected function passedValidation(): void
    {
        $values = $this->validated();
        if (isset($values['case_study'])) {
            $layout = app(PortfolioLayout::class)->validate($values['case_study']);
            $values['blocks'] = app(PortfolioLayout::class)->blocks($layout);
        }
        app(ContentDrafts::class)->validate($values, $this->route('portfolio') instanceof PortfolioItem ? $this->route('portfolio') : new PortfolioItem);
    }
}
