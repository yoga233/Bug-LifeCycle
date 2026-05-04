<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_and_invalid_login_return_same_generic_error_message(): void
    {
        $inactiveUser = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => false,
        ]);
        $activeUser = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $inactiveResponse = $this->from('/login')->post('/login', [
            'email' => $inactiveUser->email,
            'password' => 'password',
        ]);

        $invalidResponse = $this->from('/login')->post('/login', [
            'email' => $activeUser->email,
            'password' => 'wrong-password',
        ]);

        $inactiveResponse->assertSessionHasErrors(['email' => trans('auth.failed')]);
        $invalidResponse->assertSessionHasErrors(['email' => trans('auth.failed')]);
    }

    public function test_auth_attempts_are_written_to_auth_audit_log_channel(): void
    {
        Log::spy();

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        Log::shouldHaveReceived('channel')
            ->with(config('auth.audit_log_channel', 'auth_audit'))
            ->atLeast()
            ->once();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
