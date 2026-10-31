<?php

namespace App\Logics\Auth;

use App\Data\Auth\RegisterData;
use App\Http\Resources\Auth\LoginResource;
use App\Models\User;
use App\Services\OAuthTokenService;
use AxoloteSource\Logics\Logics\StoreLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use League\OAuth2\Server\Exception\OAuthServerException;
use Spatie\LaravelData\Data;

class RegisterStoreLogic extends StoreLogic
{
    public Model|User $model;

    /**
     * @var array<string, mixed>
     */
    private array $tokens = [];

    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    public function run(RegisterData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function after(): bool
    {
        try {
            $this->tokens = app(OAuthTokenService::class)->issuePasswordToken(
                $this->model->email,
                $this->input->password,
            );
        } catch (OAuthServerException) {
            return $this->error(__('Unable to issue access token'));
        }

        return true;
    }

    protected function withResource(): array|JsonResource
    {
        return new LoginResource($this->model, $this->tokens);
    }
}
