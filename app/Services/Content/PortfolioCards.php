<?php

namespace App\Services\Content;

use App\Models\PortfolioItem;
use Illuminate\Support\Carbon;

class PortfolioCards
{
    /** @return list<array{title: string, date: string, datetime: string, path: string, image: string}> */
    public function published(): array
    {
        $cards = [];
        foreach (PortfolioItem::query()->where('status', 'published')->where('published_at', '<=', now())->orderBy('sort')->orderBy('id')->get() as $portfolio) {
            $layout = $portfolio->details['case_study'] ?? null;
            if (! is_array($layout)) {
                continue;
            }
            $images = app(PortfolioLayout::class)->images($layout);
            $image = $images[$layout['thumbnail_media_id'] ?? 0] ?? null;
            if (! $image) {
                continue;
            }
            $cards[] = ['title' => $portfolio->title, 'date' => Carbon::createFromFormat('!Y-m', $layout['display_date'])->format('F Y'),
                'datetime' => $layout['display_date'], 'path' => $portfolio->path, 'image' => $image['url']];
        }

        return $cards;
    }
}
