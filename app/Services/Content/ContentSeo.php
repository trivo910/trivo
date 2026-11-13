<?php

namespace App\Services\Content;

use App\Models\Author;
use App\Models\Faq;
use App\Models\Media;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Post;
use App\Models\Service;
use App\Support\RedirectPaths;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContentSeo
{
    public const FIELDS = ['seo_title', 'seo_description', 'canonical_url', 'robots', 'og_title', 'og_description',
        'og_image_id', 'twitter_title', 'twitter_description', 'twitter_image_id', 'schema_type', 'sitemap_include', 'sitemap_priority'];

    /** @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public function validate(array $values, string $context = 'page'): array
    {
        $plain = function (string $attribute, mixed $value, Closure $fail): void {
            if (is_string($value) && preg_match('/[<>\x00-\x1F\x7F]/', $value)) {
                $fail('Use plain text without HTML or control characters.');
            }
        };
        $rules = ['content_version' => ['required', 'integer', 'min:1'],
            'canonical_url' => ['nullable', 'string', 'max:1000', 'url:https', $plain],
            'robots' => ['nullable', 'string', 'max:500'],
            'og_image_id' => ['nullable', 'integer', 'min:1'], 'twitter_image_id' => ['nullable', 'integer', 'min:1'],
            'schema_type' => ['nullable', Rule::in(array_keys($this->schemaOptions($context)))],
            'sitemap_include' => ['required', 'boolean'], 'sitemap_priority' => ['nullable', 'numeric', 'between:0,1', 'decimal:0,1']];
        foreach (['seo_title', 'og_title', 'twitter_title'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:255', $plain];
        }
        foreach (['seo_description', 'og_description', 'twitter_description'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:500', $plain];
        }
        $data = Validator::make($values, $rules)->validate();
        $canonical = $data['canonical_url'] ?? null;
        if ($canonical) {
            $parts = parse_url($canonical);
            if (! is_array($parts) || isset($parts['query']) || isset($parts['fragment']) || preg_match('/[\s\\\\]/', $canonical)
                || RedirectPaths::target($canonical) === null) {
                throw ValidationException::withMessages(['canonical_url' => 'Use an HTTPS canonical on the configured website host, without credentials, queries or fragments.']);
            }
        }
        $robots = $this->robots($data['robots'] ?? null);
        $result = [];
        foreach (self::FIELDS as $field) {
            $value = $data[$field] ?? null;
            $result[$field] = is_string($value) && trim($value) === '' ? null : $value;
        }
        $result['robots'] = $robots ? implode(',', $robots) : null;
        $result['noindex'] = in_array('noindex', $robots, true);
        $result['nofollow'] = in_array('nofollow', $robots, true);
        $result['sitemap_include'] = (bool) $result['sitemap_include'];
        if ($result['noindex'] && $result['sitemap_include']) {
            throw ValidationException::withMessages(['sitemap_include' => 'Exclude noindex pages from the sitemap.']);
        }

        return $result;
    }

    /** @return list<string> */
    private function robots(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }
        $directives = array_values(array_unique(array_map(fn (string $item) => strtolower(trim($item)), explode(',', $value))));
        foreach ($directives as $directive) {
            if (! preg_match('/^(?:index|noindex|follow|nofollow|noarchive|nosnippet|noimageindex|notranslate|max-image-preview:(?:none|standard|large)|max-(?:snippet|video-preview):(?:-1|[0-9]{1,6}))$/D', $directive)) {
                throw ValidationException::withMessages(['robots' => 'Enter supported comma-separated robots directives only.']);
            }
        }
        foreach (['index' => 'noindex', 'follow' => 'nofollow'] as $allow => $deny) {
            if (in_array($allow, $directives, true) && in_array($deny, $directives, true)) {
                throw ValidationException::withMessages(['robots' => 'Robots directives must not contradict one another.']);
            }
        }
        foreach (['max-image-preview:', 'max-snippet:', 'max-video-preview:'] as $prefix) {
            if (count(array_filter($directives, fn (string $item) => str_starts_with($item, $prefix))) > 1) {
                throw ValidationException::withMessages(['robots' => 'Use only one value for each preview directive.']);
            }
        }

        return $directives;
    }

    /** @param array<string, mixed> $values */
    public function lockImages(array $values): void
    {
        $ids = array_filter([$values['og_image_id'], $values['twitter_image_id']]);
        $images = Media::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        foreach (['og_image_id', 'twitter_image_id'] as $field) {
            if (empty($values[$field])) {
                continue;
            }
            $image = $images->get($values[$field]);
            if (! $image || $image->processing_status !== 'ready' || trim((string) $image->alt) === ''
                || ! in_array($image->mime, ['image/jpeg', 'image/png', 'image/webp', 'image/avif'], true)) {
                throw ValidationException::withMessages([$field => 'Choose a ready raster image with alt text.']);
            }
        }
    }

    /** @return array<string, mixed> */
    public function forForm(Page|Service|PortfolioItem|Post|Author|Faq $page): array
    {
        return array_intersect_key($page->getAttributes(), array_flip(self::FIELDS));
    }

    /** @return array<string, string> */
    public function schemaOptions(string $context = 'page'): array
    {
        return match ($context) {
            'service' => ['WebPage' => 'Web page', 'Service' => 'Service', 'FAQPage' => 'FAQ page'],
            'portfolio' => ['WebPage' => 'Web page', 'CreativeWork' => 'Creative work / case study'],
            'faq' => ['FAQPage' => 'FAQ page', 'WebPage' => 'Web page'],
            default => ['WebPage' => 'Web page', 'AboutPage' => 'About page', 'ContactPage' => 'Contact page', 'FAQPage' => 'FAQ page'],
        };
    }
}
