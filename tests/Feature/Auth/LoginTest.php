<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_can_login_with_valid_credentials()
    {
        $password = $this->faker->password;
        $user = User::factory()->create([
            'email' => $this->faker->email,
            'password' => bcrypt($password),
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => $password,
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role_id',
                    'created_at',
                    'updated_at',
                    'role' => [
                        'id',
                        'name',
                        'description',
                        'key',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ],
        ]);

        $responseData = $response->json('data');
        $userData = $responseData['user'];

        $response->assertJson([
            'status' => 'OK',
            'message' => null,
            'data' => [
                'user' => [
                    'email' => $user->email,
                ],
            ],
        ]);

        $this->assertEquals($user->id, $userData['id']);
        $this->assertEquals($user->email, $userData['email']);
        $this->assertArrayNotHasKey('password', $userData, 'El campo password no debe estar presente en la respuesta del usuario');

        $this->assertArrayHasKey('access_token', $responseData, 'El token de acceso no está presente en la respuesta');
        $this->assertArrayHasKey('refresh_token', $responseData, 'El refresh token no está presente en la respuesta');
        $this->assertArrayHasKey('expires_in', $responseData, 'La expiración del token no está presente en la respuesta');
    }

    public function test_login_is_rate_limited_per_account()
    {
        $this->withExceptionHandling();

        $password = 'CorrectPassword123!';
        $user = User::factory()->create([
            'email' => 'rate-limited@example.com',
            'password' => bcrypt($password),
        ]);

        for ($attempt = 1; $attempt <= 6; $attempt++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

            if ($attempt < 6) {
                $response->assertStatus(422);
            }
        }

        $response->assertStatus(429);

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => $password,
        ])->assertStatus(429);
    }

    public function test_login_rate_limit_does_not_block_other_accounts()
    {
        $this->withExceptionHandling();

        $lockedUser = User::factory()->create(['email' => 'locked@example.com']);

        for ($attempt = 1; $attempt <= 6; $attempt++) {
            $this->postJson('/api/v1/login', [
                'email' => $lockedUser->email,
                'password' => 'wrong-password',
            ]);
        }

        $password = 'CorrectPassword123!';
        $otherUser = User::factory()->create([
            'email' => 'other@example.com',
            'password' => bcrypt($password),
        ]);

        $this->postJson('/api/v1/login', [
            'email' => $otherUser->email,
            'password' => $password,
        ])->assertStatus(200);
    }

    public function test_login_is_rate_limited_per_ip()
    {
        $this->withExceptionHandling();

        for ($attempt = 1; $attempt <= 31; $attempt++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => "unknown{$attempt}@example.com",
                'password' => 'wrong-password',
            ]);
        }

        $response->assertStatus(429);
    }

    public function test_banned_user_cannot_login()
    {
        $user = User::factory()->create(['email' => 'banned-login@example.com']);
        $user->forceFill(['banned_at' => now()])->save();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Your account has been banned.');
    }
}
