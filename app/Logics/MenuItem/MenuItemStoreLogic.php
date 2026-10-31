<?php

namespace App\Logics\MenuItem;

use App\Data\MenuItem\MenuItemStoreData;
use App\Http\Resources\MenuItem\MenuItemShowResource;
use App\Models\MenuItem;
use AxoloteSource\Logics\Logics\StoreLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class MenuItemStoreLogic extends StoreLogic
{
    public Model|MenuItem $model;

    public MenuItemStoreData|Data $input;

    public function __construct(MenuItem $model)
    {
        parent::__construct($model);
    }

    public function run(MenuItemStoreData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function before(): bool
    {
        return $this->validateType();
    }

    protected function withResource(): MenuItemShowResource
    {
        return new MenuItemShowResource($this->model);
    }

    private function validateType(): bool
    {
        if ($this->input->type->isLink()) {
            if (blank($this->input->route) && blank($this->input->path)) {
                return $this->error(__('Link items must have a route or a path.'));
            }

            return true;
        }

        if (filled($this->input->route) || filled($this->input->path)) {
            return $this->error(__('Header and section items must not have a route or path.'));
        }

        return true;
    }
}
