<?php

namespace App\Logics\Menu;

use App\Data\Menu\MenuShowData;
use App\Http\Resources\Menu\MenuShowAllResource;
use App\Models\Menu;
use AxoloteSource\Logics\Logics\ShowLogic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MenuShowAllLogic extends ShowLogic
{
    public MenuShowData|Data $input;

    public function __construct(Menu $model)
    {
        parent::__construct($model);
    }

    public function run(MenuShowData|Data $input): JsonResponse|StreamedResponse
    {
        return $this->logic($input);
    }

    protected function makeQuery(): Builder
    {
        return $this->model->newQuery()
            ->where('slug', $this->input->slug)
            ->whereHas('application')
            ->with([
                'application',
                'items' => fn ($query) => $query->whereNull('parent_id')
                    ->with(['roles', 'children.roles'])
                    ->orderBy('sort_order'),
            ]);
    }

    protected function withResource(): MenuShowAllResource
    {
        return new MenuShowAllResource($this->model);
    }
}
