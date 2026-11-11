<?php

namespace App\Logics\Auth;

use App\Data\Auth\LoginData;
use App\Http\Resources\Auth\LoginResource;
use App\Models\User;
use App\Services\OAuthTokenService;
use AxoloteSource\Logics\Logics\ShowLogic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use League\OAuth2\Server\Exception\OAuthServerException;
use Spatie\LaravelData\Data;

class LoginShowLogic extends ShowLogic
{
    protected Data|LoginData $input;

    public Model|User $model;

    /**
     * @var array<string, mixed>
     */
    private array $tokens = [];

    public function __construct(protected User $user)
    {
        parent::__construct($user);
    }

    public function run(LoginData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function before(): bool
    {
        if (! $this->validCredentials()) {
            return false;
        }

        return true;
    }

    protected function makeQuery(): Builder
    {
        return User::searchByEmail($this->input->email);
    }

    protected function withResource(): array|Model|JsonResource
    {
        return new LoginResource($this->model, $this->tokens);
    }

    public function validCredentials(): bool
    {
        try {
            $this->tokens = app(OAuthTokenService::class)->issuePasswordToken(
                $this->input->email,
                $this->input->password,
            );
        } catch (OAuthServerException) {
            return $this->error(__('Invalid credentials'));
        }

        return true;
    }
}
