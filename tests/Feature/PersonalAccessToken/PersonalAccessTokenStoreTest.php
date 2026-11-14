<?php

namespace Tests\Feature\PersonalAccessToken;

use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;
use Laravel\Passport\Token;
use Tests\TestCase;

class PersonalAccessTokenStoreTest extends TestCase
{
    public function test_root_can_create_a_token_for_an_app_user(): void
    {
        $this->loginRoot();

        $user = User::factory()->create([
            'user_type_id' => UserTypeEnum::App->value,
            'role_id' => RoleEnum::Admin->value,
        ]);

        $response = $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $user->id,
            'name' => 'deploy',
            'expires_in' => 7,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'deploy');
        $response->assertJsonPath('data.user.id', $user->id);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'token',
                'token_type',
                'expires_at',
                'revoked',
                'created_at',
                'user',
            ],
        ]);

        $token = Token::query()
            ->where('user_id', $user->id)
            ->where('name', 'deploy')
            ->firstOrFail();

        $this->assertTrue($token->expires_at->between(now()->addDays(6), now()->addDays(8)));

        $this->app['auth']->forgetGuards();

        $this->withToken($response->json('data.token'))
            ->postJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);
    }

    public function test_admin_with_permission_can_create_a_token_for_an_app_user(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $app = User::factory()->create([
            'user_type_id' => UserTypeEnum::App->value,
            'role_id' => RoleEnum::Messages->value,
        ]);

        $response = $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $app->id,
            'name' => 'app-deploy',
            'expires_in' => 7,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.user.id', $app->id);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'user_id' => $app->id,
            'name' => 'app-deploy',
        ]);
    }

    public function test_root_cannot_create_a_token_for_another_human_user(): void
    {
        $this->loginRoot();

        $user = User::factory()->create();

        $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $user->id,
            'name' => 'human-token',
            'expires_in' => 7,
        ])->assertStatus(403);

        $this->assertDatabaseMissing('oauth_access_tokens', [
            'user_id' => $user->id,
            'name' => 'human-token',
        ]);
    }

    public function test_non_root_cannot_create_a_token_for_a_root_app_user(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $app = User::factory()->create([
            'user_type_id' => UserTypeEnum::App->value,
            'role_id' => RoleEnum::Root->value,
        ]);

        $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $app->id,
            'name' => 'root-app-token',
            'expires_in' => 7,
        ])->assertStatus(403);

        $this->assertDatabaseMissing('oauth_access_tokens', [
            'user_id' => $app->id,
            'name' => 'root-app-token',
        ]);
    }

    public function test_root_can_create_a_token_for_a_root_app_user(): void
    {
        $this->loginRoot();

        $app = User::factory()->create([
            'user_type_id' => UserTypeEnum::App->value,
            'role_id' => RoleEnum::Root->value,
        ]);

        $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $app->id,
            'name' => 'root-app-token',
            'expires_in' => 7,
        ])->assertStatus(201);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'user_id' => $app->id,
            'name' => 'root-app-token',
        ]);
    }

    public function test_it_accepts_each_expiry_option(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        foreach ([7, 30, 60, 90] as $days) {
            $response = $this->postJson('/api/v1/personal-access-tokens', [
                'user_id' => $admin->id,
                'name' => "token-{$days}",
                'expires_in' => $days,
            ]);

            $response->assertStatus(201);

            $token = Token::query()
                ->where('user_id', $admin->id)
                ->where('name', "token-{$days}")
                ->firstOrFail();

            $this->assertTrue($token->expires_at->between(
                now()->addDays($days - 1),
                now()->addDays($days + 1),
            ));
        }
    }

    public function test_it_can_create_a_token_that_does_not_expire(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $response = $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $admin->id,
            'name' => 'never-expires',
            'expires_in' => null,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.expires_at', null);

        $token = Token::query()
            ->where('user_id', $admin->id)
            ->where('name', 'never-expires')
            ->firstOrFail();

        $this->assertNull($token->expires_at);
    }

    public function test_it_defaults_to_no_expiration_when_expires_in_is_omitted(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $response = $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $admin->id,
            'name' => 'omitted-expiration',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.expires_at', null);

        $this->assertNull(
            Token::query()
                ->where('user_id', $admin->id)
                ->where('name', 'omitted-expiration')
                ->firstOrFail()
                ->expires_at
        );
    }

    public function test_admin_with_permission_can_create_a_token_for_themselves(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $response = $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $admin->id,
            'name' => 'self-token',
            'expires_in' => 7,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.user.id', $admin->id);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'user_id' => $admin->id,
            'name' => 'self-token',
        ]);
    }

    public function test_non_root_cannot_create_a_token_for_another_user(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $target = User::factory()->create();

        $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $target->id,
            'name' => 'cross-user',
            'expires_in' => 7,
        ])->assertStatus(403)
            ->assertJsonPath('message', __('You may not create a personal access token for another user.'));

        $this->assertDatabaseMissing('oauth_access_tokens', [
            'user_id' => $target->id,
            'name' => 'cross-user',
        ]);
    }

    public function test_non_root_cannot_create_a_token_for_root(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $root = User::factory()->create(['role_id' => RoleEnum::Root->value]);

        $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $root->id,
            'name' => 'root-token',
            'expires_in' => 7,
        ])->assertStatus(403);

        $this->assertDatabaseMissing('oauth_access_tokens', [
            'user_id' => $root->id,
            'name' => 'root-token',
        ]);
    }

    public function test_it_requires_a_valid_user(): void
    {
        $this->withExceptionHandling();

        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $response = $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => Str::uuid(),
            'name' => 'deploy',
            'expires_in' => 7,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_it_requires_a_name(): void
    {
        $this->withExceptionHandling();

        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $response = $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $admin->id,
            'expires_in' => 7,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_it_rejects_an_invalid_expiry_option(): void
    {
        $this->withExceptionHandling();

        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $response = $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $admin->id,
            'name' => 'deploy',
            'expires_in' => 99,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_user_without_permission_cannot_create_tokens(): void
    {
        $user = $this->createUser(RoleEnum::Messages);
        $target = User::factory()->create();

        $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $target->id,
            'name' => 'deploy',
        ])->assertStatus(403);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => Str::uuid(),
            'name' => 'deploy',
        ]);
    }
}
