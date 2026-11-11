<?php

namespace App\Logics\MenuItem;

use App\Data\MenuItem\MenuItemUpdateData;
use App\Http\Resources\MenuItem\MenuItemShowResource;
use App\Models\MenuItem;
use AxoloteSource\Logics\Logics\UpdateLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Data;

class MenuItemUpdateLogic extends UpdateLogic
{
    public Model|MenuItem $model;

    public MenuItemUpdateData|Data $input;

    public function __construct(MenuItem $model)
    {
        parent::__construct($model);
    }

    public function run(MenuItemUpdateData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function before(): bool
    {
        return $this->validateType() && $this->validateSlug();
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

    private function validateSlug(): bool
    {
        $slugExists = DB::table('menu_items')
            ->where('slug', $this->input->slug)
            ->where('id', '!=', $this->input->id)
            ->exists();

        if ($slugExists) {
            return $this->error(__('The slug has already been taken.'));
        }

        return true;
    }
}
