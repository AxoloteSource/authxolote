<?php

namespace Tests\Feature\UserType;

use App\Enums\RoleEnum;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class UserTypeIndexTest extends TestCase
{
    public function test_it_lists_user_types(): void
    {
        $this->loginRoot();

        $response = $this->getJson('/api/v1/user-types');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['key' => 'human']);
        $response->assertJsonFragment(['key' => 'app']);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                '*' => ['id', 'name', 'key'],
            ],
        ]);
    }

    public function test_admin_with_permission_can_list_user_types(): void
    {
        $admin = $this->loginAdmin();
        $admin->attachAction('users.index');

        $this->getJson('/api/v1/user-types')->assertOk();
    }

    public function test_user_without_permission_cannot_list_user_types(): void
    {
        $this->createUser(RoleEnum::Messages);

        $this->getJson('/api/v1/user-types')->assertStatus(403);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->getJson('/api/v1/user-types');
    }
}
