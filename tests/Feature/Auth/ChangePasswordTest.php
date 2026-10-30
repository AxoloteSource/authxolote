<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authenticated_user_can_request_password_change()
    {
        $user = User::factory()->create();
        $user->attachAction('auth.change-password');

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/change-password');

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'token',
                    'expires_at',
                ],
            ]);

        $this->assertDatabaseHas('otps', [
            'user_id' => $user->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_request_password_change()
    {
        $this->withoutExceptionHandling();
        try {
            $this->postJson('/api/v1/change-password');
        } catch (AuthenticationException $e) {
            $this->assertEquals('Unauthenticated.', $e->getMessage());

            return;
        }

        $this->fail('Expected AuthenticationException was not thrown');
    }

    public function test_user_without_permission_cannot_request_password_change()
    {
        $role = Role::factory()->create(['id' => '00000000-0000-0000-0000-000000000002']); // Un ID que no sea Root
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/change-password');

        $response->assertStatus(403);
    }

    public function test_code_debug_is_not_present_in_production()
    {
        config(['app.env' => 'production']);

        $user = User::factory()->create();
        $user->attachAction('auth.change-password');

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/change-password');

        $response->assertStatus(201)
            ->assertJsonMissing(['data' => ['code_debug']]);
    }

    public function test_code_debug_is_present_in_local()
    {
        config(['app.env' => 'local']);

        $user = User::factory()->create();
        $user->attachAction('auth.change-password');

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/change-password');

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'code_debug',
                ],
            ]);
    }
}
