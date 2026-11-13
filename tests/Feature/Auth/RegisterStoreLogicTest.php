<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleEnum;
use App\Models\User;
use Tests\TestCase;

class RegisterStoreLogicTest extends TestCase
{
    public function test_can_register(): void
    {
        $user = User::factory()->create(['role_id' => RoleEnum::Admin->value]);
        $user->attachAction('auth.register');
        $faker = $this->faker;
        $name = $faker->firstName;
        $email = $faker->unique()->safeEmail;
        $password = '$Password123';

        $payload = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'role_key' => 'admin',
        ];

        $response = $this->actingAs($user, 'api')->postJson('api/v1/register', $payload);

        $response->assertStatus(201);

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
                    'name' => $name,
                    'email' => $email,
                ],
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $userData['id'],
            'name' => $name,
            'email' => $email,
            'role_id' => RoleEnum::Admin,
        ]);
        $this->assertArrayNotHasKey('password', $userData, 'El campo password no debe estar presente en la respuesta del usuario');
        $this->assertArrayHasKey('access_token', $responseData, 'El token de acceso no está presente en la respuesta');
        $this->assertArrayHasKey('refresh_token', $responseData, 'El refresh token no está presente en la respuesta');
        $this->assertArrayHasKey('expires_in', $responseData, 'La expiración del token no está presente en la respuesta');

    }

    public function test_admin_with_register_action_cannot_register_root(): void
    {
        $user = User::factory()->create(['role_id' => RoleEnum::Admin->value]);
        $user->attachAction('auth.register');

        $response = $this->actingAs($user, 'api')->postJson('api/v1/register', [
            'name' => 'Root Escalation',
            'email' => 'root-escalation@example.com',
            'password' => '$Password123',
            'password_confirmation' => '$Password123',
            'role_key' => 'root',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('message', __('You may not assign the root role.'));
        $this->assertDatabaseMissing('users', ['email' => 'root-escalation@example.com']);
    }

    public function test_root_can_register_root(): void
    {
        $user = User::factory()->create(['role_id' => RoleEnum::Root->value]);

        $response = $this->actingAs($user, 'api')->postJson('api/v1/register', [
            'name' => 'Another Root',
            'email' => 'another-root@example.com',
            'password' => '$Password123',
            'password_confirmation' => '$Password123',
            'role_key' => 'root',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'another-root@example.com',
            'role_id' => RoleEnum::Root->value,
        ]);
    }
}
