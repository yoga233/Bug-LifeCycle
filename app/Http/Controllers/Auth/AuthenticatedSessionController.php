<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        if (! $user) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Redirect sesuai role (aturan skripsi)
        if ($user->hasRole('Project Manager')) {
            return redirect()->intended(route('pm.dashboard', absolute: false));
        }

        if ($user->hasRole('Programmer')) {
            return redirect()->intended(route('programmer.dashboard', absolute: false));
        }

        if ($user->hasRole('QA')) {
            return redirect()->intended(route('bugs.testing-queue', absolute: false));
        }

        // Fallback
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        AuthAuditLogger::write(
            event: 'logout',
            user: $user,
            request: $request,
        );

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // After logout always send user back to internal login form.
        // This avoids landing on a blank/unauthorized internal page.
        return redirect()->route('login');
    }
}
