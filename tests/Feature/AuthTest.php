<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_fresh_install_sends_you_to_setup(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertRedirect('/setup');
        $this->get('/setup')->assertOk()->assertSee('Create account');
    }

    public function test_setup_creates_the_admin_and_logs_in(): void
    {
        $this->post('/setup', [
            'name' => 'Arda',
            'email' => 'admin@example.com',
            'password' => 'correct horse',
            'password_confirmation' => 'correct horse',
        ])->assertRedirect(route('walls.create'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
    }

    public function test_setup_is_closed_once_an_account_exists(): void
    {
        User::factory()->create();

        $this->get('/setup')->assertRedirect('/login');
        $this->post('/setup', [
            'name' => 'Mallory',
            'email' => 'mallory@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertForbidden();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_and_logout(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertRedirect(route('walls.index'));
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_the_admin_pages_need_a_login(): void
    {
        $this->get('/walls')->assertRedirect('/login');
        $this->get('/walls/create')->assertRedirect('/login');
    }
}
