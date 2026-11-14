<?php

namespace App\Logics\User;

use App\Data\User\UserStoreData;
use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Http\Resources\User\UserShowResource;
use App\Models\User;
use AxoloteSource\Logics\Enums\Http;
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

    protected function before(): bool
    {
        if ($this->input->role_id === RoleEnum::Root->value && ! auth()->user()?->isRoot()) {
            return $this->error(
                __('You may not assign the root role.'),
                status: Http::Forbidden
            );
        }

        $this->input->user_type_id ??= UserTypeEnum::Human->value;

        return true;
    }

    protected function withResource(): UserShowResource
    {
        return new UserShowResource($this->model->load(['role', 'userType']));
    }
}
