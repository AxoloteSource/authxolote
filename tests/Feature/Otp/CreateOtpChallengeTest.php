<?php

namespace Tests\Feature\Otp;

use App\Jobs\SendOtpJob;
use App\Models\OtpChallenge;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateOtpChallengeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_create_otp_by_email(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/otp/challenges', [
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => 'otp-email@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.channel', 'email')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['challenge_id', 'expires_in', 'resend_in', 'channel'],
            ]);

        $this->assertDatabaseHas('otp_challenges', [
            'destination' => 'otp-email@example.com',
            'purpose' => 'login',
            'channel' => 'email',
        ]);

        $this->assertDatabaseHas('otp_deliveries', [
            'destination' => 'otp-email@example.com',
            'channel' => 'email',
            'status' => 'pending',
        ]);

        Queue::assertPushed(SendOtpJob::class);
    }

    public function test_can_create_otp_by_whatsapp(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/otp/challenges', [
            'purpose' => 'login',
            'channel' => 'whatsapp',
            'destination' => '+5213121234567',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.channel', 'whatsapp');

        $this->assertDatabaseHas('otp_challenges', [
            'destination' => '+5213121234567',
            'channel' => 'whatsapp',
        ]);
    }

    public function test_never_returns_otp_in_response(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/otp/challenges', [
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => 'otp-hidden@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonMissingPath('data.code')
            ->assertJsonMissingPath('data.otp')
            ->assertJsonMissingPath('data.code_debug')
            ->assertJsonMissingPath('otp');
    }

    public function test_otp_is_not_stored_in_plaintext(): void
    {
        Queue::fake();

        $this->postJson('/api/v1/otp/challenges', [
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => 'otp-hash@example.com',
        ])->assertStatus(201);

        $capturedCode = null;

        Queue::assertPushed(SendOtpJob::class, function (SendOtpJob $job) use (&$capturedCode) {
            $capturedCode = $job->code;

            return true;
        });

        $challenge = OtpChallenge::where('destination', 'otp-hash@example.com')->firstOrFail();

        $this->assertMatchesRegularExpression('/^\d{6}$/', $capturedCode);
        $this->assertNotEquals($capturedCode, $challenge->code_hash);
        $this->assertTrue(Hash::check($capturedCode, $challenge->code_hash));
    }

    public function test_expires_at_is_correct(): void
    {
        Queue::fake();

        $this->postJson('/api/v1/otp/challenges', [
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => 'otp-expiration@example.com',
        ])->assertStatus(201);

        $challenge = OtpChallenge::where('destination', 'otp-expiration@example.com')->firstOrFail();

        $this->assertLessThanOrEqual(2, abs($challenge->expires_at->diffInSeconds(now()->addSeconds(Setting::getOtpChallengeExpirationSeconds()))));
    }

    public function test_invalid_channel_is_rejected(): void
    {
        $this->assertValidationFails([
            'purpose' => 'login',
            'channel' => 'sms',
            'destination' => 'user@example.com',
        ], 'channel');
    }

    public function test_invalid_purpose_is_rejected(): void
    {
        $this->assertValidationFails([
            'purpose' => 'unknown',
            'channel' => 'email',
            'destination' => 'user@example.com',
        ], 'purpose');
    }

    public function test_invalid_email_destination_is_rejected(): void
    {
        $this->assertValidationFails([
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => 'not-an-email',
        ], 'destination');
    }

    public function test_invalid_phone_destination_is_rejected(): void
    {
        $this->assertValidationFails([
            'purpose' => 'login',
            'channel' => 'whatsapp',
            'destination' => '12345',
        ], 'destination');
    }

    public function test_destination_is_required(): void
    {
        $this->assertValidationFails([
            'purpose' => 'login',
            'channel' => 'email',
        ], 'destination');
    }

    public function test_server_managed_fields_are_prohibited(): void
    {
        $this->assertValidationFails([
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => 'user@example.com',
            'user_id' => '0195f51e-4a37-7087-b5f7-57d001537ab6',
        ], 'user_id');

        $this->assertValidationFails([
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => 'user@example.com',
            'attempts' => '0',
        ], 'attempts');

        $this->assertValidationFails([
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => 'user@example.com',
            'code_hash' => 'injected-hash',
        ], 'code_hash');
    }

    public function test_creating_a_new_challenge_invalidates_the_previous_one(): void
    {
        Queue::fake();
        $this->allowMultipleChallenges();

        $destination = 'otp-previous@example.com';

        $this->postJson('/api/v1/otp/challenges', [
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => $destination,
        ])->assertStatus(201);

        $first = OtpChallenge::where('destination', $destination)->latest('created_at')->firstOrFail();

        $this->postJson('/api/v1/otp/challenges', [
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => $destination,
        ])->assertStatus(201);

        $this->assertFalse($first->fresh()->isActive());

        $this->assertEquals(1, OtpChallenge::query()
            ->where('destination', $destination)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->count());
    }

    public function test_resend_cooldown_is_enforced(): void
    {
        Queue::fake();
        Setting::where('name', 'otp_resend_cooldown_seconds')->update(['value' => '60']);
        Setting::where('name', 'otp_rate_limit_max')->update(['value' => '100']);

        $destination = 'otp-cooldown@example.com';

        $this->postJson('/api/v1/otp/challenges', [
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => $destination,
        ])->assertStatus(201);

        $response = $this->postJson('/api/v1/otp/challenges', [
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => $destination,
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Please wait', $response->json('message'));
    }

    public function test_rate_limit_is_enforced(): void
    {
        Queue::fake();
        Setting::where('name', 'otp_resend_cooldown_seconds')->update(['value' => '0']);
        Setting::where('name', 'otp_rate_limit_max')->update(['value' => '2']);
        Setting::where('name', 'otp_rate_limit_decay_seconds')->update(['value' => '600']);

        $destination = 'otp-rate-limit@example.com';

        $this->postJson('/api/v1/otp/challenges', [
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => $destination,
        ])->assertStatus(201);

        $this->postJson('/api/v1/otp/challenges', [
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => $destination,
        ])->assertStatus(201);

        $response = $this->postJson('/api/v1/otp/challenges', [
            'purpose' => 'login',
            'channel' => 'email',
            'destination' => $destination,
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Too many OTP requests', $response->json('message'));
    }

    private function allowMultipleChallenges(): void
    {
        Setting::where('name', 'otp_resend_cooldown_seconds')->update(['value' => '0']);
        Setting::where('name', 'otp_rate_limit_max')->update(['value' => '100']);
    }

    private function assertValidationFails(array $payload, string $field): void
    {
        try {
            $this->postJson('/api/v1/otp/challenges', $payload);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($field, $e->errors());

            return;
        }

        $this->fail("Expected a validation error on [{$field}].");
    }
}
