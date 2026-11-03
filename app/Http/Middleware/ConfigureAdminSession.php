<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigureAdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = trim((string) config('trivo.admin_path'), '/');
        $original = ['session.same_site' => config('session.same_site'), 'session.lifetime' => config('session.lifetime')];
        if ($path !== '' && ($request->path() === $path || $request->is($path.'/*'))) {
            config(['session.same_site' => 'strict', 'session.lifetime' => 20]);
        }

        try {
            return $next($request);
        } finally {
            config($original);
        }
    }
}
