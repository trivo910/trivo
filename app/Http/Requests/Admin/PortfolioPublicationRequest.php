<?php

namespace App\Http\Requests\Admin;

use App\Models\PortfolioItem;

class PortfolioPublicationRequest extends PagePublicationRequest
{
    public function authorize(): bool
    {
        $portfolio = $this->route('portfolio');

        return $portfolio instanceof PortfolioItem && ($this->user()?->can($this->input('operation') === 'cancel' ? 'cancelPublication' : 'publish', $portfolio) ?? false);
    }
}
