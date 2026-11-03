<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Admin\AdminAuthenticator;
use App\Support\AdminAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $session = $request->session();
        if (! $user instanceof User || ! $user->is_active || ! $user->two_factor_confirmed_at
            || ! $user->hasAnyRole(AdminAccess::ROLES)
            || $session->get('admin.verified_user_id') !== $user->id
            || $session->get('admin.auth_version') !== $user->auth_version
            || (int) $session->get('admin.last_activity', 0) < now()->timestamp - 1200) {
            app(AdminAuthenticator::class)->logout($request);

            return redirect()->route('admin.login');
        }
        if (! $request->routeIs('admin.media.status')) {
            $session->put('admin.last_activity', now()->timestamp);
        }

        return $next($request);
    }
}
