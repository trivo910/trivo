<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
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
        $auth->begin($request, $request->string('email')->toString(), $request->string('password')->toString());

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request, AdminAuthenticator $auth): RedirectResponse
    {
        $auth->logout($request);

        return redirect()->route('admin.login');
    }
}
