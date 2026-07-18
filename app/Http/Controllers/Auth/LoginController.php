<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.products.index')
                : redirect()->route('account.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Force password reset for migrated users
            if (Auth::user()->must_reset_password) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                Password::sendResetLink(['email' => $credentials['email']]);

                return redirect()->route('login')
                    ->with('warning', 'Welcome back! We have upgraded our website. A password reset link has been sent to your email. Please check your inbox to set a new password.');
            }

            $intended = Auth::user()->isAdmin()
                ? route('admin.products.index')
                : route('account.dashboard');

            return redirect()->intended($intended);
        }

        return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
