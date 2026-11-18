<?php

namespace App\Logics\User;

use App\Http\Resources\User\UserIndexResource;
use App\Models\User;
use AxoloteSource\Logics\Classes\Filter;
use AxoloteSource\Logics\Data\IndexData;
use AxoloteSource\Logics\Logics\IndexLogic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class UserIndexLogic extends IndexLogic
{
    /**
     * @var array<int, string>
     */
    protected array $disallowedFilterProperties = [
        'deleted_email',
        'deleted_at',
        'password',
        'remember_token',
    ];

    /**
     * @var array<int, string>
     */
    protected array $allowOrderByFields = [
        'name',
        'email',
        'role_id',
        'user_type_id',
        'email_verified_at',
        'banned_at',
        'created_at',
        'updated_at',
        'role',
        'userType',
    ];

    /**
     * @var array<string, string>
     */
    protected array $aliasOrderBy = [
        'role' => 'role.name',
        'userType' => 'userType.name',
        'user_type_name' => 'userType.name',
    ];

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
        return $this->model->newQuery()->with(['role', 'userType']);
    }

    protected function getColumnSearch(): string
    {
        return 'email';
    }

    protected function customFilters(): array
    {
        return [
            'user_type' => function (Filter $filter) {
                $this->queryBuilder->whereHas('userType', function ($query) use ($filter) {
                    $query->where('key', $filter->value);
                });
            },
        ];
    }

    public function tableHeaders(): array
    {
        return [
            'name' => __('Nombre'),
            'email' => __('Email'),
            'role' => __('Rol'),
            'user_type_name' => __('Tipo de usuario'),
            'created_at' => __('Fecha de registro'),
            'actions' => __('Acciones'),
        ];
    }

    protected function withResource(): mixed
    {
        return UserIndexResource::collection($this->response);
    }
}
