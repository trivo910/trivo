<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\PortfolioItem;
use App\Services\Content\PublicContentRenderer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResolvePublicContentController extends Controller
{
    public function __invoke(Request $request, PublicContentRenderer $renderer): View
    {
        $path = '/'.ltrim($request->path(), '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');

        $portfolio = PortfolioItem::query()->where('path', $path)->where('status', 'published')->first();
        if ($portfolio) {
            return app(PortfolioPublicController::class)->show($portfolio);
        }
        $content = Page::query()->where('status', 'published')->where('path', $path)->first()
            ?? Post::query()->where('status', 'published')->where('path', $path)->first();

        abort_if(! $content, 404);

        return $renderer->render($content);
    }
}
