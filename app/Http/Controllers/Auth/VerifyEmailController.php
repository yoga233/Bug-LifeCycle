<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectAfterVerify($user);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return $this->redirectAfterVerify($user);
    }

    private function redirectAfterVerify($user): RedirectResponse
    {
        if (! $user) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($user->hasRole('Project Manager')) {
            return redirect()->intended(route('pm.dashboard', absolute: false).'?verified=1');
        }

        if ($user->hasRole('Programmer')) {
            return redirect()->intended(route('programmer.dashboard', absolute: false).'?verified=1');
        }

        if ($user->hasRole('QA')) {
            return redirect()->intended(route('bugs.testing-queue', absolute: false).'?verified=1');
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
