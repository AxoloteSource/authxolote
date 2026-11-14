<?php

namespace App\Logics\MenuItem;

use App\Data\MenuItem\UpdateMenuItemRoleData;
use App\Models\MenuItem;
use AxoloteSource\Logics\Logics\Logic;
use AxoloteSource\Logics\Logics\UpdateLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class MenuItemRoleUpdateLogic extends UpdateLogic
{
    public MenuItem|Model $model;

    public function __construct(MenuItem $menuItem)
    {
        parent::__construct($menuItem);
    }

    public function run(UpdateMenuItemRoleData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function action(): Logic
    {
        $this->input->active
            ? $this->model->roles()->syncWithoutDetaching([$this->input->roleId])
            : $this->model->roles()->detach($this->input->roleId);

        $this->response = collect($this->model);

        return $this;
    }
}
