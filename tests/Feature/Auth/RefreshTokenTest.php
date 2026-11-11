<?php

namespace Tests\Feature\Auth;

use App\Enums\SettingEnum;
use App\Models\Setting;
use App\Models\User;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
use Tests\TestCase;

class RefreshTokenTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function login(User $user): array
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();

        return $response->json('data');
    }

    public function test_can_refresh_access_token(): void
    {
        $tokens = $this->login(User::factory()->create());

        $response = $this->postJson('/api/v1/refresh-token', [
            'refresh_token' => $tokens['refresh_token'],
        ]);

        $response->assertOk();

        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ],
        ]);

        $refreshed = $response->json('data');

        $this->assertNotSame($tokens['access_token'], $refreshed['access_token']);
        $this->assertNotSame($tokens['refresh_token'], $refreshed['refresh_token']);
        $this->assertSame('Bearer', $refreshed['token_type']);
    }

    public function test_refresh_token_cannot_be_reused(): void
    {
        $tokens = $this->login(User::factory()->create());

        $this->postJson('/api/v1/refresh-token', [
            'refresh_token' => $tokens['refresh_token'],
        ])->assertOk();

        $this->postJson('/api/v1/refresh-token', [
            'refresh_token' => $tokens['refresh_token'],
        ])->assertStatus(401);
    }

    public function test_invalid_refresh_token_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/refresh-token', [
            'refresh_token' => 'invalid-refresh-token',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Invalid or expired refresh token',
            'data' => null,
        ]);
    }

    public function test_invalid_refresh_token_message_is_translated(): void
    {
        app()->setLocale('es');

        $response = $this->postJson('/api/v1/refresh-token', [
            'refresh_token' => 'invalid-refresh-token',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Token de actualización inválido o expirado',
        ]);
    }

    public function test_refresh_token_is_required(): void
    {
        $this->withExceptionHandling();

        $this->postJson('/api/v1/refresh-token', [])
            ->assertStatus(422);
    }

    public function test_new_access_token_can_access_a_protected_endpoint(): void
    {
        $tokens = $this->login(User::factory()->create());

        $refreshed = $this->postJson('/api/v1/refresh-token', [
            'refresh_token' => $tokens['refresh_token'],
        ])->json('data');

        $this->withHeaders([
            'Authorization' => 'Bearer '.$refreshed['access_token'],
        ])->postJson('/api/v1/me')->assertOk();
    }

    public function test_access_token_expiration_respects_the_setting(): void
    {
        Setting::query()
            ->where('name', SettingEnum::AccessTokenExpiresInMinutes->value)
            ->update(['value' => '5']);

        $user = User::factory()->create();
        $tokens = $this->login($user);

        $this->assertSame(300, $tokens['expires_in']);

        $token = Token::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        $this->assertNotNull($token?->expires_at);
        $this->assertTrue(
            $token->expires_at->between(now()->addMinutes(4), now()->addMinutes(6)),
        );
    }

    public function test_refresh_token_expiration_respects_the_setting(): void
    {
        Setting::query()
            ->where('name', SettingEnum::RefreshTokenExpiresInMinutes->value)
            ->update(['value' => '120']);

        $user = User::factory()->create();
        $this->login($user);

        $accessToken = Token::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        $token = RefreshToken::query()
            ->where('access_token_id', $accessToken->id)
            ->first();

        $this->assertNotNull($token?->expires_at);
        $this->assertTrue(
            $token->expires_at->between(now()->addMinutes(119), now()->addMinutes(121)),
        );
    }
}
