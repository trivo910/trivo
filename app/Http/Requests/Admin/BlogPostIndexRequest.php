<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class BlogPostIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Post::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:120'], 'state' => ['nullable', 'in:draft,scheduled,published,archived'], 'score' => ['nullable', 'in:any,needs_attention,good']];
    }
}
