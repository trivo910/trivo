<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class LoginProtection
{
    private function key(string $email, ?string $ip): string
    {
        return 'admin-fail:'.hash_hmac('sha256', strtolower($email).'|'.$ip, (string) config('app.key'));
    }

    public function locked(string $email, ?string $ip): bool
    {
        return Cache::has($this->key($email, $ip).':locked');
    }

    public function fail(string $email, ?string $ip): void
    {
        $key = $this->key($email, $ip);
        $attempts = RateLimiter::hit($key, 3600);
        if ($attempts >= 5) {
            Cache::put($key.':locked', true, 60 * (2 ** min(4, intdiv($attempts, 5) - 1)));
        }
    }

    public function clear(string $email, ?string $ip): void
    {
        $key = $this->key($email, $ip);
        RateLimiter::clear($key);
        Cache::forget($key.':locked');
    }
}
