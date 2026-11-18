<?php

namespace App\Logics\Menu;

use App\Http\Resources\Menu\MenuIndexResource;
use App\Models\Menu;
use AxoloteSource\Logics\Data\IndexData;
use AxoloteSource\Logics\Logics\IndexLogic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class MenuIndexLogic extends IndexLogic
{
    public Model|Menu $model;

    /**
     * @var array<int, string>
     */
    protected array $allowOrderByFields = [
        'name',
        'slug',
        'application_id',
        'size',
        'sort_order',
        'active',
        'created_at',
        'updated_at',
        'items_count',
        'application',
    ];

    /**
     * @var array<string, string>
     */
    protected array $aliasOrderBy = [
        'application' => 'application.name',
    ];

    public function __construct(Menu $model)
    {
        parent::__construct($model);
    }

    public function run(IndexData|Data $input): JsonResponse
    {
        return $this->logic($input);
    }

    public function makeQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('application')
            ->withCount('items');
    }

    /**
     * Relation ordering replaces the select clause, so the items count must be
     * added again after applying it.
     */
    public function runQueryWithOrder(Builder $queryBuilder, string $orderBy, string $direction = 'asc'): Builder
    {
        $queryBuilder = parent::runQueryWithOrder($queryBuilder, $orderBy, $direction);

        if (str_contains($orderBy, '.')) {
            $queryBuilder->withCount('items');
        }

        return $queryBuilder;
    }

    protected function tableHeaders(): array
    {
        return [
            'name' => 'Name',
            'application' => 'Application',
            'items_count' => 'Items',
            'active' => 'Active',
            'created_at' => 'Created At',
            'actions' => 'Actions',
        ];
    }

    protected function getColumnSearch(): string
    {
        return 'name';
    }

    protected function withResource(): mixed
    {
        return MenuIndexResource::collection($this->response);
    }
}
