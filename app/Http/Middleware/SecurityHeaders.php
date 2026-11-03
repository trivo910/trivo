<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(24));
        Vite::useCspNonce($nonce);
        $request->attributes->set('csp_nonce', $nonce);

        $response = $next($request);
        $adminPath = trim((string) config('trivo.admin_path'), '/');
        $isAdmin = $adminPath !== '' && ($request->path() === $adminPath || $request->is($adminPath.'/*'));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', $isAdmin ? 'DENY' : 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->remove('X-Powered-By');
        if ($isAdmin) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        if ($isAdmin || ! app()->isProduction() || $response->getStatusCode() >= 400) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        $policy = "default-src 'self'; script-src 'self' 'nonce-{$nonce}' https://www.googletagmanager.com https://www.google-analytics.com; "
            ."img-src 'self' data: https:; style-src 'self' 'unsafe-inline'; font-src 'self'; "
            .'frame-src https://www.google.com/maps https://www.youtube-nocookie.com; '
            ."connect-src 'self' https://www.google-analytics.com; base-uri 'self'; form-action 'self'; "
            .'frame-ancestors '.($isAdmin ? "'none'" : "'self'")."; object-src 'none'";
        $response->headers->set(config('trivo.csp_enforce') ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only', $policy);

        return $response;
    }
}
