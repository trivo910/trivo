<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PortfolioItem;
use App\Services\Content\PortfolioLayout;
use Illuminate\View\View;

class PortfolioPublicController extends Controller
{
    public function show(PortfolioItem $portfolio): View
    {
        abort_unless($portfolio->status === 'published' && $portfolio->published_at && $portfolio->published_at->isPast(), 404);
        $layout = $portfolio->details['case_study'] ?? null;
        abort_unless(is_array($layout), 404);

        return view('pages.portfolio-case-study', ['portfolio' => $portfolio, 'layout' => $layout,
            'images' => app(PortfolioLayout::class)->images($layout, [$portfolio->og_image_id, $portfolio->twitter_image_id])]);
    }
}
