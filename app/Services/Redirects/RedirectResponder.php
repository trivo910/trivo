<?php

namespace App\Services\Redirects;

use App\Models\Redirect;
use App\Support\RedirectPaths;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectResponder
{
    public function respond(Request $request): ?Response
    {
        $path = $request->getPathInfo();
        if (! in_array($request->method(), ['GET', 'HEAD'], true) || ! RedirectPaths::source($path)) {
            return null;
        }
        $rule = Redirect::query()->where('from_path', $path)->where('is_regex', false)->first();
        if (! $rule || $rule->from_path !== $path) {
            return null;
        }
        $code = (int) $rule->status_code;
        $target = $code === 410 ? null : RedirectPaths::target((string) $rule->to_url);
        if (! in_array($code, [301, 302, 410], true) || $code !== 410 && ($target === null || parse_url($target, PHP_URL_PATH) === $path)) {
            return null;
        }
        Redirect::query()->whereKey($rule->id)->increment('hits', 1, ['last_hit_at' => now()]);

        return $code === 410 ? response('', 410) : redirect((string) $target, $code);
    }
}
