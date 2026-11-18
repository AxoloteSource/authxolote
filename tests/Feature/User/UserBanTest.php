<?php

namespace Tests\Feature\User;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserBanTest extends TestCase
{
    public function test_it_bans_and_unbans_a_user(): void
    {
        $this->loginRoot();

        $user = User::factory()->create();

        $banResponse = $this->putJson("/api/v1/users/{$user->id}/ban");

        $banResponse->assertOk();
        $banResponse->assertJsonPath('data.id', $user->id);
        $this->assertNotNull($banResponse->json('data.banned_at'));
        $this->assertNotNull($user->refresh()->banned_at);

        $unbanResponse = $this->putJson("/api/v1/users/{$user->id}/ban");

        $unbanResponse->assertOk();
        $unbanResponse->assertJsonPath('data.banned_at', null);
        $this->assertNull($user->refresh()->banned_at);
    }

    public function test_banned_user_cannot_access_authenticated_endpoints(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $this->loginRoot();
        $this->putJson("/api/v1/users/{$user->id}/ban")->assertOk();

        $this->app['auth']->forgetGuards();

        $this->withToken($token->accessToken)
            ->postJson('/api/v1/me')
            ->assertStatus(403);
    }

    public function test_it_cannot_ban_its_own_user(): void
    {
        $root = $this->loginRoot();

        $this->putJson("/api/v1/users/{$root->id}/ban")->assertStatus(422);
    }

    public function test_non_root_cannot_ban_the_root_user(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('users.ban');

        $root = User::factory()->create(['role_id' => RoleEnum::Root->value]);

        $this->putJson("/api/v1/users/{$root->id}/ban")
            ->assertStatus(403)
            ->assertJsonPath('message', __('You may not ban the root user.'));

        $this->assertNull($root->fresh()->banned_at);
    }

    public function test_root_can_ban_another_root_user(): void
    {
        $this->loginRoot();

        $root = User::factory()->create(['role_id' => RoleEnum::Root->value]);

        $this->putJson("/api/v1/users/{$root->id}/ban")
            ->assertOk()
            ->assertJsonPath('data.id', $root->id);

        $this->assertNotNull($root->fresh()->banned_at);
    }

    public function test_it_returns_not_found_for_an_unknown_user(): void
    {
        $this->loginRoot();

        $this->putJson('/api/v1/users/'.Str::uuid().'/ban')->assertNotFound();
    }

    public function test_user_without_permission_cannot_ban_users(): void
    {
        $this->createUser(RoleEnum::Messages);
        $user = User::factory()->create();

        $this->putJson("/api/v1/users/{$user->id}/ban")->assertStatus(403);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->putJson('/api/v1/users/'.Str::uuid().'/ban');
    }
}
