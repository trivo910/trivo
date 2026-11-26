<?php

use App\Models\PortfolioItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $source = json_decode(file_get_contents(database_path('seeders/data/al-bushra-t-and-t.json')), true, 512, JSON_THROW_ON_ERROR);
        $section = collect($source['layout']['sections'] ?? [])->firstWhere('type', 'report-wide');
        if (! is_array($section)) {
            throw new RuntimeException('The Al Bushra Google Search Ads section is missing from its source data.');
        }
        unset($section['image_path']);
        $section['image_id'] = null;

        DB::transaction(function () use ($section): void {
            $item = DB::table('portfolio_items')->where('path', '/al-bushra-t-and-t')->lockForUpdate()->first();
            if (! $item) {
                return;
            }

            $details = json_decode($item->details ?? '{}', true, 512, JSON_THROW_ON_ERROR);
            $layout = $details['case_study'] ?? null;
            if (! is_array($layout) || ! is_array($layout['sections'] ?? null)) {
                throw new RuntimeException('The Al Bushra portfolio layout is missing.');
            }
            $existingIndex = collect($layout['sections'])->search(fn (array $existing): bool => ($existing['type'] ?? '') === 'report-wide'
                && str_contains($existing['title'] ?? '', 'Google Search Ads'));
            if ($existingIndex !== false) {
                $oldImage = '/images/al-bushra-google-search-ads-performance.svg';
                $newImage = '/wp-content/uploads/2026/11/image.png';
                $body = $layout['sections'][$existingIndex]['body'] ?? '';
                if (! str_contains($body, $oldImage)) {
                    return;
                }
                $layout['sections'][$existingIndex]['body'] = str_replace($oldImage, $newImage, $body);
            } else {
                $insertAt = count($layout['sections']);
                foreach ($layout['sections'] as $index => $existing) {
                    if (($existing['type'] ?? '') === 'text') {
                        $insertAt = $index;
                        break;
                    }
                }
                array_splice($layout['sections'], $insertAt, 0, [$section]);
            }
            $details['case_study'] = $layout;

            $imageIds = [$layout['thumbnail_media_id'] ?? null];
            foreach ($layout['sections'] as $existing) {
                $imageIds[] = $existing['image_id'] ?? null;
                foreach ($existing['cards'] ?? [] as $card) {
                    $imageIds[] = $card['image_id'] ?? null;
                }
            }
            $imageIds = array_values(array_unique(array_filter(array_map('intval', $imageIds))));
            $blocks = [['type' => 'paragraph', 'text' => 'Portfolio case study']];
            foreach ($imageIds as $imageId) {
                $blocks[] = ['type' => 'image', 'media_id' => $imageId];
            }

            $contentVersion = (int) $item->content_version + 1;
            $mediaId = $layout['thumbnail_media_id'] ?? null;
            $now = now();
            DB::table('portfolio_items')->where('id', $item->id)->update([
                'details' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'blocks' => json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'media_id' => $mediaId,
                'content_version' => $contentVersion,
                'updated_at' => $now,
            ]);
            DB::table('revisions')->insert([
                'revisionable_type' => PortfolioItem::class,
                'revisionable_id' => $item->id,
                'user_id' => null,
                'snapshot' => json_encode([
                    'kind' => 'content',
                    'title' => $item->title,
                    'path' => $item->path,
                    'blocks' => $blocks,
                    'media_id' => $mediaId,
                    'sort' => $item->sort,
                    'content_version' => $contentVersion,
                    'case_study' => $layout,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        // Preserve published page content and its revision if this migration is rolled back.
    }
};
