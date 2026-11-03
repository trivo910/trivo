<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Mail;

class SmtpConfigurator
{
    public function __construct(private SmtpSettings $settings) {}

    public function apply(): bool
    {
        $settings = $this->settings->get();
        if (empty($settings['host']) || empty($settings['from_email'])) {
            return false;
        }
        config([
            'mail.default' => 'trivo',
            'mail.mailers.trivo' => [
                'transport' => 'smtp', 'host' => $settings['host'], 'port' => $settings['port'],
                'scheme' => $settings['encryption'] === 'ssl' ? 'smtps' : 'smtp',
                'auto_tls' => $settings['encryption'] !== 'none', 'require_tls' => $settings['encryption'] === 'tls',
                'username' => $settings['username'] ?? null, 'password' => $settings['password'] ?? null,
                'timeout' => 10,
            ],
            'mail.from' => ['address' => $settings['from_email'], 'name' => $settings['from_name']],
            'mail.reply_to' => empty($settings['reply_to']) ? null : ['address' => $settings['reply_to'], 'name' => $settings['from_name']],
        ]);
        if (! Mail::isFake()) {
            Mail::purge('trivo');
        }

        return true;
    }
}
