<?php

namespace App\Http\Requests\Admin;

use App\Models\PortfolioItem;

class PortfolioSeoRevisionRequest extends ServiceSeoRevisionRequest
{
    public function authorize(): bool
    {
        $portfolio = $this->route('portfolio');

        return $portfolio instanceof PortfolioItem && ($this->user()?->can('updateSeo', $portfolio) ?? false);
    }
}
