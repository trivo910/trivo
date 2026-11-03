<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use App\Services\Settings\WebsiteSettings;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebsiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(app(WebsiteSettings::class)->ability((string) $this->route('section'), true), Setting::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = [];
        foreach (app(WebsiteSettings::class)->fields((string) $this->route('section')) as $name => $field) {
            $rules[$name] = match ($field['type']) {
                'media' => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at')->where('processing_status', 'ready')],
                'boolean' => ['required', 'boolean'],
                default => ['nullable', 'string', 'max:'.$field['max'], function (string $attribute, mixed $value, Closure $fail) use ($field): void {
                    if (! is_string($value)) {
                        return;
                    }
                    if (preg_match($field['type'] === 'textarea' ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F<>]/' : '/[\x00-\x1F\x7F<>]/', $value)) {
                        $fail('Use plain text without HTML or control characters.');
                    }
                }],
            };
            if ($field['type'] === 'url') {
                $rules[$name][] = 'url:https';
                $rules[$name][] = function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value)) {
                        return;
                    }
                    $parts = parse_url($value);
                    if (! is_array($parts) || isset($parts['user']) || isset($parts['pass']) || preg_match('/[\\\\\s]/', $value)) {
                        $fail('Use an HTTPS profile URL without credentials or spaces.');
                    }
                };
            }
        }
        if ((string) $this->route('section') === 'site') {
            $rules['site_name'][0] = 'required';
            $rules['contact_email'][] = 'email:rfc';
            $rules['phone'][] = 'regex:/^[+0-9(). \-]+$/D';
            $rules['whatsapp'][] = 'regex:/^\+?[1-9][0-9]{6,14}$/D';
        }
        if ((string) $this->route('section') === 'analytics') {
            foreach (['ga4_id' => '/^G-[A-Z0-9]{4,30}$/D', 'gtm_id' => '/^GTM-[A-Z0-9]{4,28}$/D',
                'meta_pixel_id' => '/^[0-9]{5,20}$/D', 'google_verification' => '/^[a-zA-Z0-9_-]+$/D',
                'bing_verification' => '/^[a-zA-Z0-9_-]+$/D'] as $key => $pattern) {
                $rules[$key][] = 'regex:'.$pattern;
            }
            $rules['ga4_id'][] = Rule::requiredIf(fn () => $this->boolean('enabled') && ! $this->filled('gtm_id') && ! $this->filled('meta_pixel_id'));
        }

        return $rules;
    }
}
