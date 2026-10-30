<?php

namespace App\Logics\User;

use App\Http\Resources\User\UserIndexResource;
use App\Models\User;
use AxoloteSource\Logics\Data\IndexData;
use AxoloteSource\Logics\Logics\IndexLogic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class UserIndexLogic extends IndexLogic
{
    public function __construct(protected User $user)
    {
        parent::__construct($user);
    }

    public function run(IndexData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    public function makeQuery(): Builder
    {
        return $this->model->newQuery()->with('role');
    }

    protected function getColumnSearch(): string
    {
        return 'email';
    }

    public function tableHeaders(): array
    {
        return [
            'name' => __('Nombre'),
            'email' => __('Email'),
            'role' => __('Rol'),
            'created_at' => __('Fecha de registro'),
            'actions' => __('Acciones'),
        ];
    }

    protected function withResource(): mixed
    {
        return UserIndexResource::collection($this->response);
    }
}
