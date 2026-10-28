<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ValidateCredentialsTest extends TestCase
{
    private function targetUser(string $email = 'target@example.com', string $password = 'Password123'): User
    {
        return User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }

    public function test_valid_credentials_return_true_without_generating_a_token(): void
    {
        $this->loginRoot();

        $target = $this->targetUser('valid.credentials@example.com');

        $response = $this->postJson('/api/v1/validate-credentials', [
            'email' => $target->email,
            'password' => 'Password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.valid', true)
            ->assertJsonMissingPath('data.access_token');

        $this->assertDatabaseCount('oauth_access_tokens', 0);
    }

    public function test_invalid_password_returns_false_without_generating_a_token(): void
    {
        $this->loginRoot();

        $target = $this->targetUser('invalid.password@example.com');

        $response = $this->postJson('/api/v1/validate-credentials', [
            'email' => $target->email,
            'password' => 'wrong-password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.valid', false);

        $this->assertDatabaseCount('oauth_access_tokens', 0);
    }

    public function test_unknown_email_returns_false(): void
    {
        $this->loginRoot();

        $response = $this->postJson('/api/v1/validate-credentials', [
            'email' => 'unknown@example.com',
            'password' => 'Password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.valid', false);

        $this->assertDatabaseCount('oauth_access_tokens', 0);
    }

    public function test_unauthenticated_user_cannot_validate_credentials(): void
    {
        $this->withExceptionHandling();

        $response = $this->postJson('/api/v1/validate-credentials', [
            'email' => 'user@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_without_the_action_cannot_validate_credentials(): void
    {
        $this->loginAdmin();

        $response = $this->postJson('/api/v1/validate-credentials', [
            'email' => 'user@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_with_the_action_can_validate_credentials(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.validate-credentials');

        $target = $this->targetUser('allowed@example.com');

        $response = $this->postJson('/api/v1/validate-credentials', [
            'email' => $target->email,
            'password' => 'Password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.valid', true);
    }

    public function test_it_validates_the_required_fields(): void
    {
        $this->loginRoot();
        $this->withExceptionHandling();

        $response = $this->postJson('/api/v1/validate-credentials', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'data' => [
                    'email',
                    'password',
                ],
            ]);
    }

    public function test_it_rate_limits_requests_from_the_same_ip(): void
    {
        $this->loginRoot();
        $this->withExceptionHandling();

        for ($attempt = 0; $attempt < 11; $attempt++) {
            $response = $this->postJson('/api/v1/validate-credentials', [
                'email' => 'unknown@example.com',
                'password' => 'Password123',
            ]);
        }

        $response->assertStatus(429);
    }
}
