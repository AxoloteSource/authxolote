<?php

namespace App\Logics\PersonalAccessToken;

use App\Data\PersonalAccessToken\PersonalAccessTokenStoreData;
use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Http\Resources\PersonalAccessToken\PersonalAccessTokenStoreResource;
use App\Models\User;
use AxoloteSource\Logics\Enums\Http;
use AxoloteSource\Logics\Logics\Logic;
use AxoloteSource\Logics\Traits\OnlyWithAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessTokenResult;
use League\OAuth2\Server\AuthorizationServer;
use Spatie\LaravelData\Data;

class PersonalAccessTokenStoreLogic extends Logic
{
    use OnlyWithAction;

    public PersonalAccessTokenStoreData|Data $input;

    private PersonalAccessTokenResult $result;

    public function run(PersonalAccessTokenStoreData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function before(): bool
    {
        $caller = auth()->user();
        $target = User::query()->find($this->input->user_id);

        if (! $target) {
            return true;
        }

        $isSelf = $this->input->user_id === $caller?->id;
        $isAppTarget = $target->user_type_id === UserTypeEnum::App->value;
        $isRootTarget = $target->role_id === RoleEnum::Root->value;

        if ($isSelf) {
            return true;
        }

        if ($caller && $isAppTarget && ($caller->isRoot() || ! $isRootTarget)) {
            return true;
        }

        return $this->error(
            __('You may not create a personal access token for another user.'),
            status: Http::Forbidden
        );
    }

    protected function action(): Logic
    {
        $user = User::query()->findOrFail($this->input->user_id);
        $defaultExpiration = Passport::$personalAccessTokensExpireIn;

        Passport::personalAccessTokensExpireIn(
            $this->input->expires_in
                ? now()->addDays($this->input->expires_in)
                : now()->addYears(100)
        );

        app()->forgetInstance(AuthorizationServer::class);

        try {
            $this->result = $user->createToken($this->input->name);

            if (is_null($this->input->expires_in)) {
                $this->result->token->forceFill(['expires_at' => null])->save();
            }
        } finally {
            Passport::$personalAccessTokensExpireIn = $defaultExpiration;
            app()->forgetInstance(AuthorizationServer::class);
        }

        return $this;
    }

    protected function withResource(): PersonalAccessTokenStoreResource
    {
        return new PersonalAccessTokenStoreResource(
            $this->result->token->load('user'),
            $this->result->accessToken,
        );
    }

    protected function response(): JsonResponse
    {
        return Response::success(
            data: $this->withResource(),
            status: Http::Created,
        );
    }
}
