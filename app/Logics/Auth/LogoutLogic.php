<?php

namespace App\Logics\Auth;

use AxoloteSource\Logics\Data\EmptyData;
use AxoloteSource\Logics\Logics\Logic;
use AxoloteSource\Logics\Traits\OnlyWithAction;
use Illuminate\Http\JsonResponse;
use Laravel\Passport\RefreshTokenRepository;

class LogoutLogic extends Logic
{
    use OnlyWithAction;

    public function run(): JsonResponse
    {
        return parent::logic(new EmptyData);
    }

    protected function action(): Logic
    {
        $token = auth()->user()->token();

        app(RefreshTokenRepository::class)->revokeRefreshTokensByAccessTokenId($token->id);

        $token->revoke();

        return $this;
    }
}
