<?php

namespace App\Services\Content;

use App\Models\Media;
use App\Support\ContentHtml;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PortfolioLayout
{
    /** @return array<string, mixed> */
    public function blank(): array
    {
        $section = fn (string $type, string $title): array => [
            'type' => $type, 'title' => $title, 'eyebrow' => '', 'body' => '', 'image_id' => null,
            'image_position' => 'right', 'button_label' => '', 'button_url' => '',
            'badge_value' => '', 'badge_label' => '', 'visible' => true,
            'padding_top' => 80, 'padding_bottom' => 80, 'cards' => [],
        ];

        return ['version' => 1, 'display_date' => now()->format('Y-m'), 'thumbnail_media_id' => null,
            'background' => '#f7f7fb', 'gradient_start' => '#034e87', 'gradient_end' => '#006ace',
            'heading_color' => '#050505', 'body_color' => '#8d8d95', 'container_width' => 1240,
            'sections' => [$section('hero', 'New portfolio profile'), $section('report', 'Project overview'),
                $section('results', 'Results'), $section('cta', 'Start your project')]];
    }

    /** @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function validate(array $input): array
    {
        $url = ['nullable', 'string', 'max:1000', function (string $key, mixed $value, \Closure $fail): void {
            if (! is_string($value)) {
                return;
            }
            $parts = parse_url($value);
            if (preg_match('/[\x00-\x20\x7f<>"\\\\]/', $value) || str_starts_with($value, '//')
                || (! str_starts_with($value, '/') && (! filter_var($value, FILTER_VALIDATE_URL) || ! is_array($parts) || ($parts['scheme'] ?? '') !== 'https'))
                || isset($parts['user']) || isset($parts['pass'])) {
                $fail('Use an internal path or a secure HTTPS URL.');
            }
        }];
        $data = Validator::make(['case_study' => $input], [
            'case_study' => ['required', 'array:version,design,display_date,thumbnail_media_id,background,gradient_start,gradient_end,heading_color,body_color,container_width,sections'],
            'case_study.design' => ['sometimes', Rule::in(['modern', 'legacy', 'industrial'])],
            'case_study.version' => ['required', 'integer', 'in:1'],
            'case_study.display_date' => ['required', 'date_format:Y-m'],
            'case_study.thumbnail_media_id' => ['nullable', 'integer', 'min:1'],
            'case_study.background' => ['required', 'hex_color'],
            'case_study.gradient_start' => ['required', 'hex_color'],
            'case_study.gradient_end' => ['required', 'hex_color'],
            'case_study.heading_color' => ['required', 'hex_color'],
            'case_study.body_color' => ['required', 'hex_color'],
            'case_study.container_width' => ['required', 'integer', 'between:960,1440'],
            'case_study.sections' => ['required', 'array', 'list', 'min:1', 'max:30'],
            'case_study.sections.*' => ['array:type,title,hide_title,eyebrow,body,image_id,image_url,image_position,heading_url,button_label,button_url,badge_value,badge_label,visible,padding_top,padding_bottom,cards'],
            'case_study.sections.*.hide_title' => ['sometimes', 'boolean'],
            'case_study.sections.*.image_url' => $url,
            'case_study.sections.*.type' => ['required', Rule::in(['hero', 'report', 'results', 'related', 'cta', 'intro', 'text', 'services', 'gallery', 'impact', 'testimonial', 'columns'])],
            'case_study.sections.*.heading_url' => $url,
            'case_study.sections.*.title' => ['required', 'string', 'max:255', 'not_regex:/[<>]/'],
            'case_study.sections.*.eyebrow' => ['nullable', 'string', 'max:150'],
            'case_study.sections.*.body' => ['nullable', 'string', 'max:20000'],
            'case_study.sections.*.image_id' => ['nullable', 'integer', 'min:1'],
            'case_study.sections.*.image_position' => ['required', Rule::in(['left', 'right'])],
            'case_study.sections.*.button_label' => ['nullable', 'string', 'max:100'],
            'case_study.sections.*.button_url' => $url,
            'case_study.sections.*.badge_value' => ['nullable', 'string', 'max:30'],
            'case_study.sections.*.badge_label' => ['nullable', 'string', 'max:100'],
            'case_study.sections.*.visible' => ['required', 'boolean'],
            'case_study.sections.*.padding_top' => ['required', 'integer', 'between:0,240'],
            'case_study.sections.*.padding_bottom' => ['required', 'integer', 'between:0,240'],
            'case_study.sections.*.cards' => ['present', 'array', 'list', 'max:20'],
            'case_study.sections.*.cards.*' => ['array:title,label,body,icon,url,image_id'],
            'case_study.sections.*.cards.*.body' => ['nullable', 'string', 'max:5000'],
            'case_study.sections.*.cards.*.icon' => ['nullable', 'string', 'max:16'],
            'case_study.sections.*.cards.*.title' => ['required', 'string', 'max:255'],
            'case_study.sections.*.cards.*.label' => ['nullable', 'string', 'max:150'],
            'case_study.sections.*.cards.*.url' => $url,
            'case_study.sections.*.cards.*.image_id' => ['nullable', 'integer', 'min:1'],
        ])->validate()['case_study'];
        foreach ($data['sections'] as &$section) {
            $section['body'] = app(ContentHtml::class)->clean($section['body'] ?? '');
            foreach ($section['cards'] as &$card) {
                if (isset($card['body'])) {
                    $card['body'] = app(ContentHtml::class)->clean($card['body']);
                }
            }
            unset($card);
        }
        unset($section);

        return $data;
    }

    /** @param array<string, mixed> $layout
     * @return list<array<string, mixed>>
     */
    public function blocks(array $layout): array
    {
        // Keep publication readiness and media protection in the existing content workflow.
        $blocks = [['type' => 'paragraph', 'text' => 'Portfolio case study']];
        foreach ($this->imageIds($layout) as $id) {
            $blocks[] = ['type' => 'image', 'media_id' => $id];
        }

        return $blocks;
    }

    /** @param array<string, mixed> $layout
     * @return list<int>
     */
    public function imageIds(array $layout): array
    {
        $ids = [$layout['thumbnail_media_id'] ?? null];
        foreach ($layout['sections'] ?? [] as $section) {
            $ids[] = $section['image_id'] ?? null;
            foreach ($section['cards'] ?? [] as $card) {
                $ids[] = $card['image_id'] ?? null;
            }
        }

        return array_values(array_unique(array_map('intval', array_filter($ids))));
    }

    /** @param array<string, mixed> $layout
     * @param list<int|null> $extraIds
     * @return array<int, array{url: string, alt: string, width: int, height: int}>
     */
    public function images(array $layout, array $extraIds = []): array
    {
        $images = [];
        foreach (Media::query()->whereIn('id', array_merge($this->imageIds($layout), array_filter($extraIds)))->where('processing_status', 'ready')->get() as $image) {
            $images[$image->id] = ['url' => $image->isLegacy() ? asset(ltrim($image->path, '/')) : Storage::disk($image->disk)->url($image->path),
                'alt' => $image->alt ?: $image->title, 'width' => $image->width ?: 770, 'height' => $image->height ?: 550];
        }

        return $images;
    }
}
