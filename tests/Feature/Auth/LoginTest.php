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
}
