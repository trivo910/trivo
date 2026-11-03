<?php

namespace App\Http\Middleware;

use App\Services\Redirects\RedirectResponder;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyConfiguredRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = app(RedirectResponder::class)->respond($request);
        } catch (QueryException) {
            // Install/migrate and storage outages must still reach the normal request handler.
            $response = null;
        }

        return $response ?? $next($request);
    }
}
