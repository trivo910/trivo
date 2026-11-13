<?php

namespace App\Services\Content;

use App\Models\Author;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Support\ContentHtml;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BlogPosts
{
    /** @param array<string, mixed> $values
     * @return array{post: array<string, mixed>, tag_ids: list<int>}
     */
    public function validate(Post $post, array $values): array
    {
        $plain = function (string $attribute, mixed $value, Closure $fail): void {
            if (is_string($value) && preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
                $fail('Use plain text without HTML or control characters.');
            }
        };
        $bodyRules = ['required', 'string', 'max:100000'];
        if ($post->legacy_wp_id === null) {
            $bodyRules[] = $plain;
        }
        $data = Validator::make($values, [
            'title' => ['required', 'string', 'max:255', $plain], 'slug' => ['required', 'string', 'max:255', 'regex:/^(?:[\p{L}\p{N}_-]|%[0-9a-fA-F]{2})+$/uD'],
            'path' => ['required', 'string', 'max:768'], 'excerpt' => ['nullable', 'string', 'max:1000', $plain],
            'body' => $bodyRules, 'author_id' => ['nullable', 'integer', 'min:1'], 'category_id' => ['nullable', 'integer', 'min:1'],
            'tag_ids' => ['present', 'array', 'max:20'], 'tag_ids.*' => ['integer', 'min:1', 'distinct'], 'featured_media_id' => ['nullable', 'integer', 'min:1'],
            'focus_keyword' => ['nullable', 'string', 'max:191', $plain], 'secondary_keywords' => ['present', 'array', 'max:4'], 'secondary_keywords.*' => ['nullable', 'string', 'max:191', $plain],
            'seo_title' => ['nullable', 'string', 'max:255', $plain], 'seo_description' => ['nullable', 'string', 'max:500', $plain],
            'is_cornerstone' => ['required', 'boolean'], 'show_toc' => ['required', 'boolean'],
        ])->validate();
        if (! app(ContentPaths::class)->available($data['path'], $post->exists ? $post->id : null, 'posts')) {
            throw ValidationException::withMessages(['path' => 'Use a unique exact path without reserved routes or aliases.']);
        }
        if ($post->exists && $post->path !== $data['path']) {
            throw ValidationException::withMessages(['path' => 'Keep the saved post path. URL changes need a reviewed redirect workflow.']);
        }
        $data['excerpt'] = filled($data['excerpt'] ?? null) ? trim((string) $data['excerpt']) : null;
        $data['focus_keyword'] = filled($data['focus_keyword'] ?? null) ? trim((string) $data['focus_keyword']) : null;
        $data['seo_title'] = filled($data['seo_title'] ?? null) ? trim((string) $data['seo_title']) : null;
        $data['seo_description'] = filled($data['seo_description'] ?? null) ? trim((string) $data['seo_description']) : null;
        $data['is_cornerstone'] = (bool) $data['is_cornerstone'];
        $data['show_toc'] = (bool) $data['show_toc'];
        $data['secondary_keywords'] = array_values(array_unique(array_filter(array_map(fn ($keyword) => is_string($keyword) ? trim($keyword) : '', $data['secondary_keywords']), fn (string $keyword) => $keyword !== '')));
        $data['body'] = $post->legacy_wp_id !== null
            ? app(ContentHtml::class)->clean($data['body'])
            : trim($data['body']);
        if ($data['body'] === '') {
            throw ValidationException::withMessages(['body' => 'Enter the post body.']);
        }
        foreach (['author_id' => Author::class, 'category_id' => Category::class] as $key => $model) {
            $data[$key] = empty($data[$key]) ? null : (int) $data[$key];
            if ($data[$key] !== null && ! $model::query()->whereKey($data[$key])->exists()) {
                throw ValidationException::withMessages([$key => $key === 'author_id' ? 'Choose an active author.' : 'Choose an active category.']);
            }
        }
        $tagIds = array_values(array_unique(array_map('intval', $data['tag_ids'])));
        if (count($tagIds) !== Tag::query()->whereIn('id', $tagIds)->count()) {
            throw ValidationException::withMessages(['tag_ids' => 'Choose active tags only.']);
        }
        $data['featured_media_id'] = empty($data['featured_media_id']) ? null : (int) $data['featured_media_id'];
        if ($data['featured_media_id'] !== null && ! Media::query()->whereKey($data['featured_media_id'])->where('processing_status', 'ready')->whereNotNull('alt')->exists()) {
            throw ValidationException::withMessages(['featured_media_id' => 'Choose a ready image with alt text.']);
        }
        unset($data['tag_ids']);

        return ['post' => $data, 'tag_ids' => $tagIds];
    }
}
