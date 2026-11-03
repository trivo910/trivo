<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class AdminAuthenticator
{
    public function __construct(private Google2FA $totp, private AuthAudit $audit, private LoginProtection $protection) {}

    public function begin(Request $request, string $email, #[\SensitiveParameter] string $password): User
    {
        $email = strtolower($email);
        $user = User::query()->where('email', $email)->first();
        // Valid dummy bcrypt value avoids skipping the password check for unknown accounts.
        $hash = $user->password ?? '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $matches = Hash::check($password, $hash);
        if (! $matches || ! $user || ! $user->is_active || ! $user->hasAnyRole(AdminAccess::ROLES)
            || $this->protection->locked($email, $request->ip())) {
            $this->protection->fail($email, $request->ip());
            $this->audit->record('login_failed', null, $request->ip());
            throw ValidationException::withMessages(['email' => 'Unable to sign in. Check your details or try again later.']);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('admin.pending', ['id' => $user->id, 'expires' => now()->timestamp + 300,
            'version' => $user->auth_version]);

        return $user;
    }

    public function pending(Request $request): ?User
    {
        $pending = $request->session()->get('admin.pending');
        if (! is_array($pending) || ($pending['expires'] ?? 0) < now()->timestamp) {
            return null;
        }
        $user = User::query()->find((int) ($pending['id'] ?? 0));

        return $user && $user->is_active && $user->hasAnyRole(AdminAccess::ROLES)
            && $user->auth_version === ($pending['version'] ?? null) ? $user : null;
    }

    public function enrollmentSecret(Request $request): string
    {
        if (! $request->session()->has('admin.enrollment_secret')) {
            $request->session()->put('admin.enrollment_secret', $this->totp->generateSecretKey(32));
        }

        return (string) $request->session()->get('admin.enrollment_secret');
    }

    public function provisioningUri(User $user, string $secret): string
    {
        return $this->totp->getQRCodeUrl('Trivo Admin', $user->email, $secret);
    }

    /** @return list<string> */
    public function enroll(Request $request, #[\SensitiveParameter] string $code): array
    {
        $pending = $this->pending($request);
        abort_unless($pending && ! $pending->two_factor_confirmed_at, 403);
        $secret = $this->enrollmentSecret($request);
        $step = $this->totp->verifyKeyNewer($secret, $code, 0, 1);
        if (! is_int($step) || $this->protection->locked($pending->email, $request->ip())) {
            $this->failCode($pending, $request);
        }
        $codes = array_map(fn () => strtoupper(bin2hex(random_bytes(4)).'-'.bin2hex(random_bytes(4))), range(1, 10));
        DB::transaction(function () use ($pending, $secret, $step, $codes) {
            $user = User::query()->lockForUpdate()->findOrFail($pending->id);
            abort_if($user->two_factor_confirmed_at !== null || ! $user->is_active, 403);
            abort_unless($user->auth_version === $pending->auth_version && $user->hasAnyRole(AdminAccess::ROLES), 403);
            $user->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => now(),
                'two_factor_last_used_step' => $step,
                'two_factor_recovery_codes' => array_map(fn (string $code) => Hash::make($code), $codes)])->save();
        });
        $user = $pending->fresh() ?? abort(403);
        $this->audit->record('two_factor_enrolled', $user, $request->ip());
        $this->complete($request, $user);

        return $codes;
    }

    public function challenge(Request $request, #[\SensitiveParameter] string $code): void
    {
        $pending = $this->pending($request);
        abort_unless($pending && $pending->two_factor_confirmed_at, 403);
        $valid = DB::transaction(function () use ($pending, $code, $request): bool {
            $user = User::query()->lockForUpdate()->findOrFail($pending->id);
            if (! $user->is_active || ! $user->hasAnyRole(AdminAccess::ROLES) || ! $user->two_factor_confirmed_at
                || $user->auth_version !== $pending->auth_version || $this->protection->locked($user->email, $request->ip())) {
                return false;
            }
            $step = preg_match('/^\d{6}$/D', $code)
                ? $this->totp->verifyKeyNewer((string) $user->two_factor_secret, $code, $user->two_factor_last_used_step ?? 0, 1)
                : false;
            if (is_int($step)) {
                $user->forceFill(['two_factor_last_used_step' => $step])->save();

                return true;
            }
            $codes = $user->recoveryCodeHashes();
            foreach ($codes as $index => $hash) {
                if (Hash::check(strtoupper(trim($code)), $hash)) {
                    unset($codes[$index]);
                    $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();
                    $this->audit->record('recovery_code_used', $user, $request->ip());

                    return true;
                }
            }

            return false;
        });
        if (! $valid) {
            $this->failCode($pending, $request);
        }
        $this->complete($request, $pending->fresh() ?? abort(403));
    }

    private function failCode(User $user, Request $request): never
    {
        $this->protection->fail($user->email, $request->ip());
        $this->audit->record('two_factor_failed', $user, $request->ip());
        throw ValidationException::withMessages(['code' => 'Unable to verify this code. Try again with a fresh code.']);
    }

    private function complete(Request $request, User $user): void
    {
        abort_unless($user->is_active && $user->two_factor_confirmed_at && $user->hasAnyRole(AdminAccess::ROLES), 403);
        Auth::login($user, false);
        $request->session()->regenerate(true);
        $request->session()->forget(['admin.pending', 'admin.enrollment_secret']);
        $request->session()->put(['admin.verified_user_id' => $user->id, 'admin.auth_version' => $user->auth_version,
            'admin.last_activity' => now()->timestamp]);
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();
        $this->protection->clear($user->email, $request->ip());
        $this->audit->record('login_succeeded', $user, $request->ip());
    }

    public function logout(Request $request): void
    {
        $user = $request->user();
        $this->audit->record('logged_out', $user instanceof User ? $user : null, $request->ip());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
