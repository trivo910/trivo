<?php

namespace App\Actions\Admin;

use App\Models\Setting;
use App\Services\Mail\SmtpConfigurator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SaveSmtpSettings
{
    /** @param array<string, mixed> $values */
    public function handle(#[\SensitiveParameter] array $values): void
    {
        Gate::authorize('updateMail', Setting::class);
        DB::transaction(function () use ($values) {
            $setting = Setting::query()->where('key', 'mail.smtp')->lockForUpdate()->first()
                ?? new Setting(['key' => 'mail.smtp', 'group' => 'mail']);
            $previous = $setting->getAttribute('value');
            $data = array_intersect_key($values, array_flip(['host', 'port', 'encryption', 'username',
                'from_email', 'from_name', 'reply_to', 'queue_enabled']));
            $data['queue_enabled'] = (bool) $data['queue_enabled'];
            $data['port'] = (int) $data['port'];
            $data['password'] = ! empty($values['clear_password']) ? null
                : ($values['password'] ?? (is_array($previous) ? ($previous['password'] ?? null) : null));
            $setting->value = $data;
            $setting->save();
        });
        app(SmtpConfigurator::class)->apply();
        activity('settings')->causedBy(auth()->user())->event('smtp_settings_updated')->log('smtp_settings_updated');
    }
}
