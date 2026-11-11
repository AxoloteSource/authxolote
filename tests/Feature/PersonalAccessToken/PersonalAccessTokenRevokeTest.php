<?php

namespace Tests\Feature\PersonalAccessToken;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;
use Tests\TestCase;

class PersonalAccessTokenRevokeTest extends TestCase
{
    public function test_admin_can_revoke_any_users_token(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.revoke');

        $user = User::factory()->create();
        $token = $user->createToken('deploy');

        $response = $this->postJson("/api/v1/personal-access-tokens/{$token->token->id}/revoke");

        $response->assertOk();
        $response->assertJsonPath('data.revoked', true);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $token->token->id,
            'revoked' => true,
        ]);

        $this->app['auth']->forgetGuards();

        $this->withExceptionHandling()
            ->withToken($token->accessToken)
            ->postJson('/api/v1/me')
            ->assertUnauthorized();
    }

    public function test_it_returns_not_found_for_an_unknown_token(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.revoke');

        $this->postJson('/api/v1/personal-access-tokens/'.Str::random(40).'/revoke')
            ->assertNotFound();
    }

    public function test_user_without_permission_cannot_revoke_tokens(): void
    {
        $this->createUser(RoleEnum::Messages);

        $user = User::factory()->create();
        $token = $user->createToken('deploy');

        $this->postJson("/api/v1/personal-access-tokens/{$token->token->id}/revoke")
            ->assertStatus(403);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->postJson('/api/v1/personal-access-tokens/'.Str::random(40).'/revoke');
    }
}
