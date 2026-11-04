<?php

namespace App\Services\Content;

use App\Models\Media;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Service;
use App\Support\RedirectPaths;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContentDrafts
{
    /** @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function validate(array $input, Page|Service|PortfolioItem|null $page = null): array
    {
        $plain = function (string $attribute, mixed $value, Closure $fail): void {
            if (is_string($value) && preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
                $fail('Use plain text without HTML or control characters.');
            }
        };
        $data = Validator::make($input, [
            'title' => ['required', 'string', 'max:255', $plain],
            'path' => ['required', 'string', 'max:768'],
            'content_version' => [$page?->exists ? 'required' : 'nullable', 'integer', 'min:1'],
            'blocks' => ['present', 'array', 'max:50'],
            'blocks.*' => ['required', 'array:type,text,media_id,label,url'],
            'blocks.*.type' => ['required', Rule::in(['heading', 'paragraph', 'image', 'link'])],
            'blocks.*.text' => ['nullable', 'string', 'max:8000', $plain],
            'blocks.*.media_id' => ['nullable', 'integer', 'min:1'],
            'blocks.*.label' => ['nullable', 'string', 'max:150', $plain],
            'blocks.*.url' => ['nullable', 'string', 'max:1000'],
        ])->validate();
        if (! array_is_list($data['blocks'])) {
            throw ValidationException::withMessages(['blocks' => 'Sections must be an ordered list.']);
        }
        if (! app(ContentPaths::class)->available($data['path'], $page?->id, $page?->getTable() ?? 'pages')) {
            throw ValidationException::withMessages(['path' => 'Use a unique content path without reserved routes, aliases, queries or unsafe characters.']);
        }
        if ($page && ($page->legacy_wp_id !== null || $page->publication_locked_at !== null) && $page->path !== $data['path']) {
            throw ValidationException::withMessages(['path' => 'Keep the original imported content path. URL changes require a reviewed redirect.']);
        }
        $blocks = [];
        foreach ($data['blocks'] as $index => $block) {
            $type = $block['type'];
            $clean = ['type' => $type];
            if (in_array($type, ['heading', 'paragraph'], true)) {
                if (trim($block['text'] ?? '') === '') {
                    throw ValidationException::withMessages(['blocks.'.$index.'.text' => 'Enter the section text.']);
                }
                if ($type === 'heading' && mb_strlen($block['text']) > 250) {
                    throw ValidationException::withMessages(['blocks.'.$index.'.text' => 'Section headings may contain up to 250 characters.']);
                }
                $clean['text'] = trim($block['text']);
            } elseif ($type === 'image') {
                if (empty($block['media_id'])) {
                    throw ValidationException::withMessages(['blocks.'.$index.'.media_id' => 'Choose an image.']);
                }
                $clean['media_id'] = (int) $block['media_id'];
            } else {
                $url = $block['url'] ?? '';
                $parts = parse_url($url);
                $internal = str_starts_with($url, '/') && ! preg_match('/[<>"]/', $url) && RedirectPaths::target($url) !== null;
                $external = filter_var($url, FILTER_VALIDATE_URL) && is_array($parts) && ($parts['scheme'] ?? '') === 'https'
                    && ! isset($parts['user']) && ! isset($parts['pass']) && ! preg_match('/[<>"\x00-\x20\x7F\\\\]/', $url);
                if (trim($block['label'] ?? '') === '' || ! ($internal || $external)) {
                    throw ValidationException::withMessages(['blocks.'.$index.'.url' => 'Enter a link label and safe site-relative or HTTPS URL.']);
                }
                $clean['label'] = trim($block['label']);
                $clean['url'] = $url;
            }
            $blocks[] = $clean;
        }
        $data['blocks'] = $blocks;

        return $data;
    }

    /** @param list<array<string, mixed>> $blocks */
    public function lockImages(array $blocks): void
    {
        $ids = array_values(array_unique(array_column(array_filter($blocks, fn (array $block) => $block['type'] === 'image'), 'media_id')));
        $images = Media::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        foreach ($blocks as $index => $block) {
            if ($block['type'] !== 'image') {
                continue;
            }
            $image = $images->get($block['media_id']);
            if (! $image || $image->processing_status !== 'ready' || trim((string) $image->alt) === ''
                || ! in_array($image->mime, ['image/jpeg', 'image/png', 'image/webp', 'image/avif'], true)) {
                throw ValidationException::withMessages(['blocks.'.$index.'.media_id' => 'Choose a ready raster image with alt text.']);
            }
        }
    }
}
