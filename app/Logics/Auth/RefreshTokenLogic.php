<?php

namespace App\Logics\Auth;

use App\Data\Auth\RefreshTokenData;
use App\Http\Resources\Auth\RefreshTokenResource;
use App\Services\OAuthTokenService;
use AxoloteSource\Logics\Enums\Http;
use AxoloteSource\Logics\Logics\Logic;
use AxoloteSource\Logics\Traits\OnlyWithAction;
use Illuminate\Http\JsonResponse;
use League\OAuth2\Server\Exception\OAuthServerException;
use Spatie\LaravelData\Data;

class RefreshTokenLogic extends Logic
{
    use OnlyWithAction;

    protected Data|RefreshTokenData $input;

    /**
     * @var array<string, mixed>
     */
    private array $tokens = [];

    public function run(RefreshTokenData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function before(): bool
    {
        try {
            $this->tokens = app(OAuthTokenService::class)->issueRefreshToken(
                $this->input->refresh_token,
            );
        } catch (OAuthServerException) {
            return $this->error(__('Invalid or expired refresh token'), status: Http::Unauthenticated);
        }

        return true;
    }

    protected function action(): Logic
    {
        return $this;
    }

    protected function withResource(): RefreshTokenResource
    {
        return new RefreshTokenResource($this->tokens);
    }
}
