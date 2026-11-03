<?php

namespace App\Rules;

use App\Services\Media\RasterInspector;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class RasterUpload implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('Choose a valid image upload.');

            return;
        }
        try {
            app(RasterInspector::class)->inspect($value->getRealPath(), $value->getClientOriginalExtension(), $value->getClientOriginalName());
        } catch (ValidationException $exception) {
            $fail($exception->errors()['image'][0]);
        }
    }
}
