<?php

namespace App\Logics\Auth;

use App\Data\Auth\RegisterData;
use App\Http\Resources\Auth\LoginResource;
use App\Models\User;
use AxoloteSource\Logics\Logics\StoreLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\LaravelData\Data;

class RegisterStoreLogic extends StoreLogic
{
    public Model|User $model;

    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    public function run(RegisterData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function withResource(): array|JsonResource
    {
        return new LoginResource($this->model);
    }
}
