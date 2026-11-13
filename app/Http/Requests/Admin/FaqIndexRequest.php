<?php

namespace App\Http\Requests\Admin;

use App\Models\Faq;

class FaqIndexRequest extends PageIndexRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Faq::class) ?? false;
    }
}
