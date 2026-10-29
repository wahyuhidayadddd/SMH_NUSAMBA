<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class CmsAuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('cms_authenticated', false)) {
            return redirect()->route('cms.index');
        }

        return view('cms.login', [
            'isConfigured' => filled(config('cms.admin_email')) && filled(config('cms.admin_password')),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);
        $key = 'cms-login:'.strtolower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()
                ->withErrors(['email' => 'Terlalu banyak percobaan masuk. Coba lagi setelah satu menit.'])
                ->onlyInput('email');
        }

        $emailMatches = hash_equals(
            strtolower((string) config('cms.admin_email')),
            strtolower($credentials['email']),
        );
        $passwordMatches = hash_equals(
            (string) config('cms.admin_password'),
            $credentials['password'],
        );

        if (! filled(config('cms.admin_email')) || ! filled(config('cms.admin_password')) || ! $emailMatches || ! $passwordMatches) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['email' => 'Email atau kata sandi tidak sesuai.'])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put('cms_authenticated', true);

        return redirect()->intended(route('cms.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('cms.login');
    }
}
