<?php

namespace App\Http\Requests\Admin;

use App\Models\Faq;

class FaqMutationRequest extends ServiceMutationRequest
{
    public function authorize(): bool
    {
        $faq = $this->route('faq');
        $ability = match ($this->route()?->getName()) {
            'admin.faqs.restore' => 'restore', 'admin.faqs.revision' => 'update', default => 'delete',
        };

        return $faq instanceof Faq && ($this->user()?->can($ability, $faq) ?? false);
    }
}
