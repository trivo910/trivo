<?php

namespace App\Http\Requests\Admin;

use App\Models\Media;
use App\Rules\RasterUpload;
use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Media::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['image' => ['bail', 'required', 'file', 'max:8192', new RasterUpload],
            'alt' => ['required', 'string', 'max:1000'], 'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'folder' => ['nullable', 'string', 'max:80', 'regex:/\A[\pL\pN][\pL\pN _-]*\z/u']];
    }
}
