<?php

namespace Tests\Feature\PersonalAccessToken;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;
use Laravel\Passport\Token;
use Tests\TestCase;

class PersonalAccessTokenStoreTest extends TestCase
{
    public function test_admin_can_create_a_token_for_another_user(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $user = User::factory()->create();

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

    public function test_it_accepts_each_expiry_option(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $user = User::factory()->create();

        foreach ([7, 30, 60, 90] as $days) {
            $response = $this->postJson('/api/v1/personal-access-tokens', [
                'user_id' => $user->id,
                'name' => "token-{$days}",
                'expires_in' => $days,
            ]);

            $response->assertStatus(201);

            $token = Token::query()
                ->where('user_id', $user->id)
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

        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $user->id,
            'name' => 'never-expires',
            'expires_in' => null,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.expires_at', null);

        $token = Token::query()
            ->where('user_id', $user->id)
            ->where('name', 'never-expires')
            ->firstOrFail();

        $this->assertNull($token->expires_at);
    }

    public function test_it_requires_a_valid_user(): void
    {
        $this->withExceptionHandling();

        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $response = $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => Str::uuid(),
            'name' => 'deploy',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_it_requires_a_name(): void
    {
        $this->withExceptionHandling();

        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $user->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_it_rejects_an_invalid_expiry_option(): void
    {
        $this->withExceptionHandling();

        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.store');

        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/personal-access-tokens', [
            'user_id' => $user->id,
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
