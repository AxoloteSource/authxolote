<?php

namespace App\Logics\PersonalAccessToken;

use App\Data\PersonalAccessToken\PersonalAccessTokenDeleteData;
use AxoloteSource\Logics\Enums\Http;
use AxoloteSource\Logics\Logics\Logic;
use AxoloteSource\Logics\Traits\OnlyWithAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Laravel\Passport\Token;
use Spatie\LaravelData\Data;

class PersonalAccessTokenDeleteLogic extends Logic
{
    use OnlyWithAction;

    public PersonalAccessTokenDeleteData|Data $input;

    private Token $token;

    public function run(PersonalAccessTokenDeleteData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function initializer(): bool
    {
        $token = Token::query()
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
        $this->token->delete();

        return $this;
    }

    protected function response(): JsonResponse
    {
        return Response::success(
            data: null,
            status: Http::NoContent,
        );
    }
}
