<?php

namespace Tests\Feature\User;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserDeleteTest extends TestCase
{
    public function test_it_soft_deletes_a_user_and_frees_the_email(): void
    {
        $this->loginRoot();

        $user = User::factory()->create(['email' => 'delete-me@example.com']);

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertNoContent();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_email' => 'delete-me@example.com',
            'email' => 'deleted+'.$user->id.'@deleted.local',
        ]);

        $this->postJson('/api/v1/users', [
            'name' => 'New User',
            'email' => 'delete-me@example.com',
            'role_id' => RoleEnum::Admin->value,
            'password' => 'password123',
        ])->assertStatus(201);
    }

    public function test_non_root_cannot_delete_the_root_user(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('users.destroy');

        $root = User::factory()->create(['role_id' => RoleEnum::Root->value]);

        $this->deleteJson("/api/v1/users/{$root->id}")
            ->assertStatus(403)
            ->assertJsonPath('message', __('You may not delete the root user.'));

        $this->assertNotSoftDeleted('users', ['id' => $root->id]);
    }

    public function test_root_can_delete_another_root_user(): void
    {
        $this->loginRoot();

        $root = User::factory()->create(['role_id' => RoleEnum::Root->value]);

        $this->deleteJson("/api/v1/users/{$root->id}")->assertNoContent();

        $this->assertSoftDeleted('users', ['id' => $root->id]);
    }

    public function test_it_returns_not_found_for_an_unknown_user(): void
    {
        $this->loginRoot();

        $this->deleteJson('/api/v1/users/'.Str::uuid())->assertNotFound();
    }

    public function test_it_cannot_delete_its_own_user(): void
    {
        $root = $this->loginRoot();

        $this->deleteJson("/api/v1/users/{$root->id}")->assertStatus(422);
    }

    public function test_user_without_permission_cannot_delete_users(): void
    {
        $this->createUser(RoleEnum::Messages);
        $user = User::factory()->create();

        $this->deleteJson("/api/v1/users/{$user->id}")->assertStatus(403);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->deleteJson('/api/v1/users/'.Str::uuid());
    }
}
