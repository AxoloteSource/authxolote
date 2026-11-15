<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RecoveryPasswordTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_user_can_request_password_recovery_with_existing_email()
    {
        $user = User::factory()->create(['email' => 'test1@example.com']);

        $response = $this->postJson('/api/v1/recovery-password', [
            'email' => 'test1@example.com',
        ]);

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

    public function test_unauthenticated_user_gets_success_response_with_non_existent_email()
    {
        $initialCount = DB::table('otps')->count();

        $response = $this->postJson('/api/v1/recovery-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'token',
                    'expires_at',
                ],
            ]);

        // Should not have any NEW OTP for a user that doesn't exist
        $this->assertDatabaseCount('otps', $initialCount);
    }

    public function test_it_does_not_return_the_otp_hash_outside_production()
    {
        config(['app.env' => 'local']);
        User::factory()->create(['email' => 'test2@example.com']);

        $response = $this->postJson('/api/v1/recovery-password', [
            'email' => 'test2@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'expires_at',
                ],
            ])
            ->assertJsonMissingPath('data.code_debug');
    }

    public function test_it_does_not_return_the_otp_hash_in_production()
    {
        config(['app.env' => 'production']);
        User::factory()->create(['email' => 'test3@example.com']);

        $response = $this->postJson('/api/v1/recovery-password', [
            'email' => 'test3@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonMissingPath('data.code_debug');
    }

    public function test_recovery_password_requires_valid_email()
    {
        $this->withoutExceptionHandling();
        try {
            $this->postJson('/api/v1/recovery-password', [
                'email' => 'not-an-email',
            ]);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());

            return;
        }

        $this->fail('Expected ValidationException was not thrown');
    }
}
