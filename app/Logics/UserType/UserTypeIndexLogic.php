<?php

namespace App\Logics\UserType;

use App\Http\Resources\UserType\UserTypeIndexResource;
use App\Models\UserType;
use AxoloteSource\Logics\Data\IndexData;
use AxoloteSource\Logics\Logics\IndexLogic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class UserTypeIndexLogic extends IndexLogic
{
    protected bool $withPagination = false;

    protected array $allowOrderByFields = ['name'];

    public function __construct(protected UserType $userType)
    {
        parent::__construct($userType);
    }

    public function run(IndexData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    public function makeQuery(): Builder
    {
        return $this->model->newQuery()->orderBy('name');
    }

    protected function withResource(): mixed
    {
        return UserTypeIndexResource::collection($this->response);
    }
}
