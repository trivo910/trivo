<?php

namespace App\Http\Requests\Admin;

use App\Models\Media;

class UpdateMediaRequest extends UploadMediaRequest
{
    public function authorize(): bool
    {
        $media = $this->route('media');

        return $media instanceof Media && ($this->user()?->can('update', $media) ?? false);
    }

    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['image']);

        return $rules;
    }
}
