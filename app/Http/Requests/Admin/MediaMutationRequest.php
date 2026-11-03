<?php

namespace App\Http\Requests\Admin;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;

class MediaMutationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $media = $this->route('media');
        $ability = match ($this->route()?->getName()) {
            'admin.media.restore' => 'restore', 'admin.media.retry' => 'retry', default => 'delete',
        };

        return $media instanceof Media && ($this->user()?->can($ability, $media) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
