<?php

namespace App\Services\Mail;

use Throwable;

class SmtpErrorSanitizer
{
    /** @param array<string, mixed> $settings */
    public function message(Throwable $exception, #[\SensitiveParameter] array $settings): string
    {
        $message = $exception->getMessage();
        $secrets = [];
        foreach (['username', 'password'] as $key) {
            if (is_string($settings[$key] ?? null) && $settings[$key] !== '') {
                $secrets[] = $settings[$key];
                $secrets[] = rawurlencode($settings[$key]);
                $secrets[] = base64_encode($settings[$key]);
            }
        }
        if (! empty($settings['username']) && ! empty($settings['password'])) {
            $secrets[] = base64_encode("\0".$settings['username']."\0".$settings['password']);
            $secrets[] = base64_encode($settings['username']."\0".$settings['username']."\0".$settings['password']);
        }
        usort($secrets, fn (string $a, string $b) => strlen($b) <=> strlen($a));
        $message = str_replace($secrets, '[redacted]', $message);
        $message = preg_replace('~[a-z][a-z0-9+.-]*://[^\s]+~i', '[connection redacted]', $message) ?? '';
        $message = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+/i', '[email redacted]', $message) ?? '';

        return mb_substr(preg_replace('/[\x00-\x1F\x7F]+/', ' ', $message) ?? '', 0, 1000);
    }
}
