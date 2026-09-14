<?php

namespace Tests\Feature\User;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserStoreTest extends TestCase
{
    public function test_it_can_create_a_user(): void
    {
        $this->loginRoot();

        $response = $this->postJson('/api/v1/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role_id' => RoleEnum::Admin->value,
            'password' => 'password123',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role_id' => RoleEnum::Admin->value,
        ]);

        $this->assertTrue(Hash::check('password123', User::where('email', 'jane@example.com')->first()->password));
        $this->assertSame('Jane Doe', $response->json('data.name'));
    }

    public function test_it_requires_name(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->postJson('/api/v1/users', [
            'email' => 'jane@example.com',
            'role_id' => RoleEnum::Admin->value,
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_it_requires_a_valid_email(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->postJson('/api/v1/users', [
            'name' => 'Jane Doe',
            'email' => 'not-an-email',
            'role_id' => RoleEnum::Admin->value,
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_it_rejects_duplicate_emails(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->postJson('/api/v1/users', [
            'name' => 'Jane Doe',
            'email' => 'duplicate@example.com',
            'role_id' => RoleEnum::Admin->value,
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_it_requires_role_id(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->postJson('/api/v1/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_it_requires_password(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->postJson('/api/v1/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role_id' => RoleEnum::Admin->value,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->postJson('/api/v1/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role_id' => RoleEnum::Admin->value,
            'password' => 'password123',
        ]);
    }
}
