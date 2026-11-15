<?php

namespace App\Logics\MenuItem;

use App\Data\MenuItem\MenuItemUpdateData;
use App\Http\Resources\MenuItem\MenuItemShowResource;
use App\Models\Menu;
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
        return $this->validateType()
            && $this->validateSlug()
            && $this->validateMenu()
            && $this->validateMenuChange()
            && $this->validateParent();
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

    private function validateMenu(): bool
    {
        $menuExists = Menu::query()
            ->whereKey($this->input->menu_id)
            ->exists();

        if (! $menuExists) {
            return $this->error(__('The selected menu is invalid.'));
        }

        return true;
    }

    private function validateMenuChange(): bool
    {
        if ($this->input->menu_id === $this->model->menu_id) {
            return true;
        }

        if ($this->model->children()->exists()) {
            return $this->error(__('A menu item with children cannot be moved to another menu.'));
        }

        return true;
    }

    private function validateParent(): bool
    {
        $parentId = $this->input->parent_id;

        if (is_null($parentId)) {
            return true;
        }

        if ($parentId === $this->input->id) {
            return $this->error(__('A menu item cannot be its own parent.'));
        }

        /** @var MenuItem|null $parent */
        $parent = MenuItem::query()->find($parentId);

        if (is_null($parent)) {
            return $this->error(__('The selected parent is invalid.'));
        }

        if ($parent->menu_id !== $this->input->menu_id) {
            return $this->error(__('The parent menu item does not belong to the given menu.'));
        }

        if ($this->parentCreatesCycle($parent)) {
            return $this->error(__('The menu item hierarchy contains a cycle.'));
        }

        return true;
    }

    private function parentCreatesCycle(MenuItem $parent): bool
    {
        $visited = [];
        $current = $parent;

        while ($current instanceof MenuItem) {
            if ($current->id === $this->input->id) {
                return true;
            }

            if (isset($visited[$current->id])) {
                return true;
            }

            $visited[$current->id] = true;

            $current = is_null($current->parent_id)
                ? null
                : MenuItem::query()->find($current->parent_id);
        }

        return false;
    }
}
