<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class AdminIpAllow
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = config('trivo.admin_allowed_ips', []);
        abort_if($allowed !== [] && ! IpUtils::checkIp((string) $request->ip(), $allowed), 404);

        return $next($request);
    }
}
