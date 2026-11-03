<?php

namespace App\Services\Redirects;

use App\Models\NotFoundLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotFoundRecorder
{
    public function record(Request $request): void
    {
        $path = $request->getPathInfo();
        if (preg_match('~^/(?:password|reset|token)(?:/|$)~i', $path)) {
            return;
        }
        $path = preg_replace('/[A-Z0-9._%+-]+(?:@|%40)[A-Z0-9.-]+/i', '[email-redacted]', $path) ?? '/';
        $path = mb_substr(preg_replace('/[\x00-\x1F\x7F]/', '', $path) ?? '/', 0, 768);
        NotFoundLog::query()->upsert([['path' => $path, 'referrer' => null,
            'ua_hash' => hash_hmac('sha256', (string) $request->userAgent(), (string) config('app.key')),
            'count' => 1, 'last_seen' => now()]], ['path'], ['count' => DB::raw('count + 1'), 'last_seen', 'ua_hash']);
    }
}
