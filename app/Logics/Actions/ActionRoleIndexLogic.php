<?php

namespace App\Logics\Actions;

use App\Http\Resources\Actions\RoleActionResource;
use App\Models\Action;
use AxoloteSource\Logics\Classes\Filter;
use AxoloteSource\Logics\Data\IndexData;
use AxoloteSource\Logics\Logics\IndexLogic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\LaravelData\Data;

class ActionRoleIndexLogic extends IndexLogic
{
    public function __construct(protected Action $action)
    {
        parent::__construct($action);
    }

    protected array $allowOrderByFields = [
        'description',
        'name',
    ];

    public function run(IndexData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    public function makeQuery(): Builder
    {
        $query = $this->model->newQuery();
        $query->withExists(['roles as active' => function ($query) {
            $query->where('role_id', $this->input->id);
        }]);

        return $query;
    }

    protected function withResource(): AnonymousResourceCollection
    {
        return RoleActionResource::collection($this->response);
    }

    public function searchActive(Filter $filter): void
    {
        $this->queryBuilder->having('active', '=', filter_var($filter->value, FILTER_VALIDATE_BOOLEAN));
    }

    protected function customFilters(): array
    {
        return [
            'active' => fn (Filter $filter) => $this->searchActive($filter),
            'name' => fn (Filter $filter) => $this->queryBuilder->where('name', 'like', "%{$filter->value}%"),
        ];
    }

    protected function tableHeaders(): array
    {
        return [
            'description' => __('Descripción'),
            'name' => __('Nombre'),
            'actions' => __('Acciones'),
        ];
    }
}
