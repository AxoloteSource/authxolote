<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegisterAuthTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test that register route requires authentication.
     */
    public function test_unauthenticated_user_cannot_access_register(): void
    {
        $this->withExceptionHandling();
        $response = $this->postJson('/api/v1/register', []);
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_authenticated_user_without_register_action_cannot_register(): void
    {
        $user = User::factory()->create(['role_id' => RoleEnum::Messages->value]);

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/register', [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role_key' => 'admin',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('data.action', 'auth.register');
    }
}
