<?php

namespace App\Logics\User;

use App\Data\User\UserStoreData;
use App\Http\Resources\User\UserShowResource;
use App\Models\User;
use AxoloteSource\Logics\Logics\StoreLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class UserStoreLogic extends StoreLogic
{
    public Model|User $model;

    public UserStoreData|Data $input;

    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function run(UserStoreData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function withResource(): UserShowResource
    {
        return new UserShowResource($this->model->load('role'));
    }
}
