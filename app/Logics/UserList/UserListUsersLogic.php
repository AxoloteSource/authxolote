<?php

namespace App\Logics\UserList;

use App\Data\UserList\UserListUsersData;
use App\Http\Resources\UserList\UserListUsersResource;
use App\Models\User;
use App\Models\UserList;
use AxoloteSource\Logics\Enums\Http;
use AxoloteSource\Logics\Logics\IndexLogic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class UserListUsersLogic extends IndexLogic
{
    public Model|UserList $model;

    public UserListUsersData|Data $input;

    private ?UserList $list = null;

    public function __construct(UserList $model)
    {
        parent::__construct($model);
    }

    public function run(UserListUsersData|Data $input): JsonResponse
    {
        return $this->logic($input);
    }

    protected function before(): bool
    {
        $this->list = $this->model->newQuery()->find($this->input->id);

        if (is_null($this->list)) {
            return $this->error(message: 'Not Found', status: Http::NotFound);
        }

        return true;
    }

    public function makeQuery(): Builder
    {
        if ($this->list->isAllUsers()) {
            return User::query();
        }

        return User::query()->whereIn('users.id', $this->list->users()->select('users.id'));
    }

    protected function getColumnSearch(): string
    {
        return 'name';
    }

    protected function tableHeaders(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
        ];
    }

    protected function withResource(): mixed
    {
        return UserListUsersResource::collection($this->response);
    }
}
