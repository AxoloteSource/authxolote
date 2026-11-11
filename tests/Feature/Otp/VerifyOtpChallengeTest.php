<?php

namespace Tests\Feature\Otp;

use App\Models\OtpChallenge;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class VerifyOtpChallengeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_correct_otp_is_verified(): void
    {
        $challenge = OtpChallenge::factory()->create();

        $response = $this->postJson("/api/v1/otp/challenges/{$challenge->id}/verify", [
            'code' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.verified', true);

        $this->assertNotNull($challenge->fresh()->verified_at);
    }

    public function test_incorrect_otp_is_rejected_and_increments_attempts(): void
    {
        $challenge = OtpChallenge::factory()->create();

        $response = $this->postJson("/api/v1/otp/challenges/{$challenge->id}/verify", [
            'code' => '000000',
        ]);

        $response->assertStatus(422);
        $this->assertEquals('Invalid OTP code.', $response->json('message'));
        $this->assertEquals(1, $challenge->fresh()->attempts);
    }

    public function test_attempts_increment_on_each_failed_attempt(): void
    {
        $challenge = OtpChallenge::factory()->create();

        $this->postJson("/api/v1/otp/challenges/{$challenge->id}/verify", ['code' => '000000']);
        $this->postJson("/api/v1/otp/challenges/{$challenge->id}/verify", ['code' => '111111']);

        $this->assertEquals(2, $challenge->fresh()->attempts);
    }

    public function test_expired_otp_is_rejected(): void
    {
        $challenge = OtpChallenge::factory()->expired()->create();

        $response = $this->postJson("/api/v1/otp/challenges/{$challenge->id}/verify", [
            'code' => '123456',
        ]);

        $response->assertStatus(422);
        $this->assertEquals('This OTP has expired.', $response->json('message'));
        $this->assertNull($challenge->fresh()->verified_at);
    }

    public function test_already_used_otp_is_rejected(): void
    {
        $challenge = OtpChallenge::factory()->verified()->create();

        $response = $this->postJson("/api/v1/otp/challenges/{$challenge->id}/verify", [
            'code' => '123456',
        ]);

        $response->assertStatus(422);
        $this->assertEquals('This OTP has already been used.', $response->json('message'));
    }

    public function test_unknown_challenge_is_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->postJson('/api/v1/otp/challenges/'.Str::uuid().'/verify', [
            'code' => '123456',
        ]);
    }

    public function test_challenge_is_blocked_after_max_attempts(): void
    {
        $challenge = OtpChallenge::factory()->maxAttemptsReached()->create();

        $response = $this->postJson("/api/v1/otp/challenges/{$challenge->id}/verify", [
            'code' => '123456',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Too many attempts', $response->json('message'));
        $this->assertNull($challenge->fresh()->verified_at);
    }

    public function test_attempts_do_not_increment_after_success(): void
    {
        $challenge = OtpChallenge::factory()->create();

        $this->postJson("/api/v1/otp/challenges/{$challenge->id}/verify", ['code' => '123456'])
            ->assertStatus(200);

        $this->postJson("/api/v1/otp/challenges/{$challenge->id}/verify", ['code' => '123456'])
            ->assertStatus(422);

        $challenge->refresh();
        $this->assertEquals(0, $challenge->attempts);
    }

    public function test_otp_can_only_be_used_once(): void
    {
        $challenge = OtpChallenge::factory()->create();

        $this->postJson("/api/v1/otp/challenges/{$challenge->id}/verify", ['code' => '123456'])
            ->assertStatus(200);

        $response = $this->postJson("/api/v1/otp/challenges/{$challenge->id}/verify", ['code' => '123456']);

        $response->assertStatus(422);
        $this->assertEquals('This OTP has already been used.', $response->json('message'));
    }
}
