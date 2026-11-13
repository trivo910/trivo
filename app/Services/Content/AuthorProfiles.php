<?php

namespace App\Services\Content;

use App\Models\Author;
use App\Models\Media;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthorProfiles
{
    /** @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public function validate(Author $author, array $values): array
    {
        $plain = function (string $attribute, mixed $value, Closure $fail): void {
            if (is_string($value) && preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
                $fail('Use plain text without HTML or control characters.');
            }
        };
        $data = Validator::make($values, [
            'name' => ['required', 'string', 'max:255', $plain],
            'path' => ['required', 'string', 'max:768'],
            'bio' => ['nullable', 'string', 'max:10000', $plain],
            'avatar_id' => ['nullable', 'integer', 'min:1'],
            'socials' => ['nullable', 'array'],
            'socials.*' => ['nullable', 'string', 'max:2048'],
        ])->validate();
        if (! app(ContentPaths::class)->available($data['path'], $author->exists ? $author->id : null, 'authors')) {
            throw ValidationException::withMessages(['path' => 'Use a unique exact path without reserved routes or aliases.']);
        }
        if ($author->exists && $author->path !== $data['path']) {
            throw ValidationException::withMessages(['path' => 'Keep the saved author path. URL changes need a reviewed redirect workflow.']);
        }
        $data['bio'] ??= null;
        $data['avatar_id'] = empty($data['avatar_id']) ? null : (int) $data['avatar_id'];
        if ($data['avatar_id'] !== null && ! Media::query()->whereKey($data['avatar_id'])->where('processing_status', 'ready')->whereNotNull('alt')->exists()) {
            throw ValidationException::withMessages(['avatar_id' => 'Choose a ready image with alt text from the image library.']);
        }
        $allowed = ['website', 'linkedin', 'x', 'instagram'];
        $socials = [];
        foreach ((array) ($data['socials'] ?? []) as $name => $url) {
            if (! in_array($name, $allowed, true) || ! is_string($url) || trim($url) === '') {
                continue;
            }
            $parsed = parse_url($url);
            if (($parsed['scheme'] ?? null) !== 'https' || ! isset($parsed['host']) || isset($parsed['user'], $parsed['pass'])
                || preg_match('/[\x00-\x1F\x7F]/', $url)) {
                throw ValidationException::withMessages(['socials.'.$name => 'Use a complete HTTPS URL without credentials.']);
            }
            $socials[$name] = $url;
        }
        $data['socials'] = $socials === [] ? null : $socials;

        return $data;
    }
}
