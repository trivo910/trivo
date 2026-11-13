<?php

namespace App\Http\Requests\Admin;

use App\Models\Faq;
use App\Services\Content\ContentSeo;

class FaqSeoRequest extends PageSeoRequest
{
    public function authorize(): bool { $faq = $this->route('faq'); return $faq instanceof Faq && ($this->user()?->can('updateSeo', $faq) ?? false); }
    protected function passedValidation(): void { app(ContentSeo::class)->validate($this->validated(), 'faq'); }
}
