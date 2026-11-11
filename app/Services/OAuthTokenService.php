<?php

namespace App\Services;

use App\Models\Setting;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;
use Nyholm\Psr7\Response as Psr7Response;
use Nyholm\Psr7\ServerRequest;

class OAuthTokenService
{
    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function issuePasswordToken(string $email, string $password): array
    {
        $client = $this->client();

        return $this->request([
            'grant_type' => 'password',
            'client_id' => $client->getKey(),
            'client_secret' => $client->secret,
            'username' => $email,
            'password' => $password,
            'scope' => '',
        ]);
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function issueRefreshToken(string $refreshToken): array
    {
        $client = $this->client();

        return $this->request([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $client->getKey(),
            'client_secret' => $client->secret,
            'scope' => '',
        ]);
    }

    /**
     * @param  array<string, string>  $payload
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    protected function request(array $payload): array
    {
        Passport::tokensExpireIn(now()->addMinutes(Setting::getAccessTokenExpiresInMinutes()));
        Passport::refreshTokensExpireIn(now()->addMinutes(Setting::getRefreshTokenExpiresInMinutes()));

        app()->forgetInstance(AuthorizationServer::class);

        $request = (new ServerRequest('POST', 'not-important'))->withParsedBody($payload);

        $response = app(AuthorizationServer::class)
            ->respondToAccessTokenRequest($request, new Psr7Response);

        return json_decode((string) $response->getBody(), true);
    }

    protected function client(): Client
    {
        return Client::query()
            ->where('password_client', true)
            ->where('revoked', false)
            ->latest('created_at')
            ->firstOrFail();
    }
}
