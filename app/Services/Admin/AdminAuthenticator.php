<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthenticator
{
    public function __construct(private AuthAudit $audit, private LoginProtection $protection) {}

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
        $this->complete($request, $user);

        return $user;
    }

    private function complete(Request $request, User $user): void
    {
        abort_unless($user->is_active && $user->hasAnyRole(AdminAccess::ROLES), 403);
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
