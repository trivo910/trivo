<?php

namespace App\Http\Middleware;

use App\Services\Admin\AdminAuthenticator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PendingAdminLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(AdminAuthenticator::class)->pending($request)) {
            $request->session()->forget(['admin.pending', 'admin.enrollment_secret']);

            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
