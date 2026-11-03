<?php

namespace App\Services\Mail;

use App\Models\Setting;

class SmtpSettings
{
    /** @return array<string, mixed> */
    public function get(): array
    {
        $value = Setting::query()->where('key', 'mail.smtp')->first()?->getAttribute('value');

        return is_array($value) ? $value : [];
    }

    /** @return array<string, mixed> */
    public function forForm(): array
    {
        $values = $this->get();
        $values['password_configured'] = ! empty($values['password']);
        unset($values['password']);

        return $values;
    }
}
