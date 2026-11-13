<?php

namespace App\Http\Requests\Admin;

use App\Models\Faq;
use Illuminate\Foundation\Http\FormRequest;

class FaqSeoRevisionRequest extends FormRequest
{
    public function authorize(): bool { $faq = $this->route('faq'); return $faq instanceof Faq && ($this->user()?->can('updateSeo', $faq) ?? false); }
    /** @return array<string, list<string>> */ public function rules(): array { return ['content_version' => ['required', 'integer', 'min:1']]; }
}
