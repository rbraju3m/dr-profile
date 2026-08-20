<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/appointments')->assertRedirect('/admin/login');
    }

    public function test_an_admin_can_sign_in(): void
    {
        $user = User::factory()->create([
            'email' => 'boss@example.com',
            'password' => Hash::make('secret-password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->post('/admin/login', [
            'email' => 'boss@example.com',
            'password' => 'secret-password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'boss@example.com', 'password' => Hash::make('secret-password')]);

        $this->post('/admin/login', [
            'email' => 'boss@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_deactivated_account_cannot_sign_in(): void
    {
        User::factory()->create([
            'email' => 'gone@example.com',
            'password' => Hash::make('secret-password'),
            'is_active' => false,
        ]);

        $this->post('/admin/login', [
            'email' => 'gone@example.com',
            'password' => 'secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_editors_cannot_reach_admin_only_screens(): void
    {
        $editor = User::factory()->create(['role' => 'editor', 'is_active' => true]);

        $this->actingAs($editor)->get('/admin')->assertOk();
        $this->actingAs($editor)->get('/admin/appointments')->assertOk();
        $this->actingAs($editor)->get('/admin/settings')->assertForbidden();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
    }

    public function test_admins_can_reach_admin_only_screens(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->get('/admin/settings')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    public function test_signing_out_ends_the_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }
}
