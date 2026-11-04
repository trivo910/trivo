<?php

namespace App\Http\Requests\Admin;

use App\Models\PortfolioItem;

class PortfolioIndexRequest extends PageIndexRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', PortfolioItem::class) ?? false;
    }
}
