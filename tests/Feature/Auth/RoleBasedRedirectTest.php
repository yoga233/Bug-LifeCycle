<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RoleBasedRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_after_login_based_on_role(): void
    {
        Role::create(['name' => 'Project Manager', 'guard_name' => 'web']);
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);
        Role::create(['name' => 'QA', 'guard_name' => 'web']);

        $pm = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
        $pm->syncRoles(['Project Manager']);

        $dev = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
        $dev->syncRoles(['Programmer']);

        $qa = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
        $qa->syncRoles(['QA']);

        // login endpoint redirects (pastikan logout di antaranya karena /login adalah route guest)
        $this->post('/login', ['email' => $pm->email, 'password' => 'password'])->assertRedirect('/project-manager/dashboard');
        $this->post('/logout');

        $this->post('/login', ['email' => $dev->email, 'password' => 'password'])->assertRedirect('/programmer/dashboard');
        $this->post('/logout');

        $this->post('/login', ['email' => $qa->email, 'password' => 'password'])->assertRedirect('/bugs/testing-queue');
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => false,
        ]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
    }

    public function test_unverified_internal_user_is_redirected_to_email_verification_notice(): void
    {
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $user = User::factory()->create([
            'is_active' => true,
            'email_verified_at' => null,
        ]);
        $user->syncRoles(['Programmer']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/programmer/dashboard');

        $this->get('/programmer/dashboard')->assertRedirect('/verify-email');
    }

    public function test_logout_is_written_to_auth_audit_log_channel(): void
    {
        Role::create(['name' => 'Programmer', 'guard_name' => 'web']);

        $user = User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $user->syncRoles(['Programmer']);

        Log::spy();

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        Log::shouldHaveReceived('channel')
            ->with(config('auth.audit_log_channel', 'auth_audit'))
            ->atLeast()
            ->once();
    }
}
