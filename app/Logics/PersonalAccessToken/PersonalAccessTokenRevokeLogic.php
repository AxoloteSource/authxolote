<?php

namespace App\Logics\PersonalAccessToken;

use App\Data\PersonalAccessToken\PersonalAccessTokenRevokeData;
use App\Http\Resources\PersonalAccessToken\PersonalAccessTokenIndexResource;
use AxoloteSource\Logics\Enums\Http;
use AxoloteSource\Logics\Logics\Logic;
use AxoloteSource\Logics\Traits\OnlyWithAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Laravel\Passport\Token;
use Spatie\LaravelData\Data;

class PersonalAccessTokenRevokeLogic extends Logic
{
    use OnlyWithAction;

    public PersonalAccessTokenRevokeData|Data $input;

    private Token $token;

    public function run(PersonalAccessTokenRevokeData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function initializer(): bool
    {
        $token = Token::query()
            ->with('user')
            ->whereKey($this->input->id)
            ->whereHas('client', fn (Builder $query) => $query->where('personal_access_client', true))
            ->first();

        if (is_null($token)) {
            return $this->error(message: 'Not Found', status: Http::NotFound);
        }

        $this->token = $token;

        return true;
    }

    protected function action(): Logic
    {
        $this->token->forceFill(['revoked' => true])->save();

        return $this;
    }

    protected function withResource(): PersonalAccessTokenIndexResource
    {
        return new PersonalAccessTokenIndexResource($this->token);
    }
}
