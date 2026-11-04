<?php

namespace App\Http\Requests\Admin;

use App\Models\PortfolioItem;
use App\Services\Content\ContentSeo;

class PortfolioSeoRequest extends ServiceSeoRequest
{
    public function authorize(): bool
    {
        $portfolio = $this->route('portfolio');

        return $portfolio instanceof PortfolioItem && ($this->user()?->can('updateSeo', $portfolio) ?? false);
    }

    protected function passedValidation(): void
    {
        app(ContentSeo::class)->validate($this->validated(), 'portfolio');
    }
}
