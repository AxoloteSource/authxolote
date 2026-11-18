<?php

namespace Tests\Feature\PersonalAccessToken;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;
use Tests\TestCase;

class PersonalAccessTokenDeleteTest extends TestCase
{
    public function test_admin_can_delete_any_users_token(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.destroy');

        $user = User::factory()->create();
        $token = $user->createToken('deploy');

        $response = $this->deleteJson("/api/v1/personal-access-tokens/{$token->token->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('oauth_access_tokens', [
            'id' => $token->token->id,
        ]);
    }

    public function test_it_returns_not_found_for_an_unknown_token(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.destroy');

        $this->deleteJson('/api/v1/personal-access-tokens/'.Str::random(40))
            ->assertNotFound();
    }

    public function test_user_without_permission_cannot_delete_tokens(): void
    {
        $this->createUser(RoleEnum::Messages);

        $user = User::factory()->create();
        $token = $user->createToken('deploy');

        $this->deleteJson("/api/v1/personal-access-tokens/{$token->token->id}")
            ->assertStatus(403);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->deleteJson('/api/v1/personal-access-tokens/'.Str::random(40));
    }
}
