<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogPostPublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post && ($this->user()?->can($this->input('operation') === 'cancel' ? 'cancelPublication' : 'publish', $post) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['content_version' => ['required', 'integer', 'min:1'], 'operation' => ['required', Rule::in(['publish', 'schedule', 'cancel'])],
            'scheduled_for' => [Rule::excludeIf($this->input('operation') !== 'schedule'), 'required', 'date_format:Y-m-d\TH:i', 'after:now']];
    }
}
