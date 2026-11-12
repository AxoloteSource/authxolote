<?php

namespace Tests\Feature\PersonalAccessToken;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class PersonalAccessTokenIndexTest extends TestCase
{
    public function test_it_lists_tokens_from_all_users(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $firstUser->createToken('first-token');
        $secondUser->createToken('second-token');

        $this->loginRoot();

        $response = $this->getJson('/api/v1/personal-access-tokens');

        $response->assertStatus(206);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['name' => 'first-token']);
        $response->assertJsonFragment(['name' => 'second-token']);
        $response->assertJsonMissingPath('data.0.token');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'expires_at',
                    'revoked',
                    'created_at',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'user_type_id',
                    ],
                ],
            ],
            'columns',
        ]);
    }

    public function test_it_filters_tokens_by_user_id(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $firstUser->createToken('first-token');
        $secondUser->createToken('second-token');

        $this->loginRoot();

        $response = $this->getJson('/api/v1/personal-access-tokens?user_id='.$firstUser->id);

        $response->assertStatus(206);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.user.id', $firstUser->id);
        $response->assertJsonPath('data.0.name', 'first-token');
    }

    public function test_it_ignores_login_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('personal-token');

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $this->loginRoot();

        $response = $this->getJson('/api/v1/personal-access-tokens');

        $response->assertStatus(206);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'personal-token');
    }

    public function test_admin_with_permission_can_list_tokens(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('auth.personal-access-tokens.index');

        $this->getJson('/api/v1/personal-access-tokens')->assertStatus(206);
    }

    public function test_user_without_permission_cannot_list_tokens(): void
    {
        $this->createUser(RoleEnum::Messages);

        $this->getJson('/api/v1/personal-access-tokens')->assertStatus(403);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->getJson('/api/v1/personal-access-tokens');
    }
}
