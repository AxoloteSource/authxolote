<?php

namespace App\Logics\Roles;

use App\Models\Role;
use AxoloteSource\Logics\Data\IndexData;
use AxoloteSource\Logics\Logics\IndexLogic;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class RoleIndexLogic extends IndexLogic
{
    public function __construct(protected Role $role)
    {
        parent::__construct($role);
    }

    protected array $allowOrderByFields = [
        'name',
        'description',
        'key',
    ];

    public function run(IndexData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    public function tableHeaders(): array
    {
        return [
            'name' => __('Nombre'),
            'description' => __('Descripción'),
            'key' => __('key'),
            'actions' => __('Acciones'),
        ];
    }
}
