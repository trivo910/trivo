<?php

namespace App\Http\Middleware;

use App\Services\Redirects\NotFoundRecorder;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordNotFound
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $admin = trim((string) config('trivo.admin_path'), '/');
        if ($response->getStatusCode() === 404 && in_array($request->method(), ['GET', 'HEAD'], true)
            && ($admin === '' || ! $request->is($admin, $admin.'/*'))) {
            try {
                app(NotFoundRecorder::class)->record($request);
            } catch (QueryException) {
                // Unavailable logging storage must not turn a 404 into a 500.
            }
        }

        return $response;
    }
}
