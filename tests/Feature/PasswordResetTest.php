<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_accessible(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    public function test_user_receives_reset_link_email(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $response = $this->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('password.email'), ['email' => 'reset@example.com']);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'reset@example.com']);
    }

    public function test_reset_link_is_not_sent_for_unknown_email(): void
    {
        $response = $this->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('password.email'), ['email' => 'naoexiste@example.com']);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $token = Password::broker()->createToken($user);

        $response = $this->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('password.store'), [
                'email' => 'reset@example.com',
                'token' => $token,
                'password' => 'novasenha123',
                'password_confirmation' => 'novasenha123',
            ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('novasenha123', $user->fresh()->password));
    }

    public function test_user_cannot_reset_password_with_invalid_token(): void
    {
        User::factory()->create(['email' => 'reset@example.com']);

        $response = $this->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('password.store'), [
                'email' => 'reset@example.com',
                'token' => 'token-invalido',
                'password' => 'novasenha123',
                'password_confirmation' => 'novasenha123',
            ]);

        $response->assertSessionHasErrors('email');
    }
}
