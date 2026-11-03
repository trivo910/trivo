<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\TwoFactorRequest;
use App\Services\Admin\AdminAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('admin.auth.login');
    }

    public function authenticate(LoginRequest $request, AdminAuthenticator $auth): RedirectResponse
    {
        $user = $auth->begin($request, $request->string('email')->toString(), $request->string('password')->toString());

        return redirect()->route($user->two_factor_confirmed_at ? 'admin.challenge' : 'admin.enroll');
    }

    public function enrollment(Request $request, AdminAuthenticator $auth): View|RedirectResponse
    {
        $user = $auth->pending($request) ?? abort(403);
        if ($user->two_factor_confirmed_at) {
            return redirect()->route('admin.challenge');
        }
        $secret = $auth->enrollmentSecret($request);

        return view('admin.auth.enroll', ['secret' => $secret, 'uri' => $auth->provisioningUri($user, $secret)]);
    }

    public function enroll(TwoFactorRequest $request, AdminAuthenticator $auth): RedirectResponse
    {
        $codes = $auth->enroll($request, $request->string('code')->toString());
        $request->session()->flash('admin.recovery_codes', $codes);

        return redirect()->route('admin.recovery-codes');
    }

    public function challenge(Request $request, AdminAuthenticator $auth): View|RedirectResponse
    {
        if (! $auth->pending($request)?->two_factor_confirmed_at) {
            return redirect()->route('admin.enroll');
        }

        return view('admin.auth.challenge');
    }

    public function verify(TwoFactorRequest $request, AdminAuthenticator $auth): RedirectResponse
    {
        $auth->challenge($request, $request->string('code')->toString());

        return redirect()->route('admin.dashboard');
    }

    public function recoveryCodes(Request $request): View|RedirectResponse
    {
        $codes = $request->session()->pull('admin.recovery_codes');

        return $codes ? view('admin.auth.recovery-codes', ['codes' => $codes]) : redirect()->route('admin.dashboard');
    }

    public function logout(Request $request, AdminAuthenticator $auth): RedirectResponse
    {
        $auth->logout($request);

        return redirect()->route('admin.login');
    }
}
