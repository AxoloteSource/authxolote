<?php

namespace App\Logics\Menu;

use App\Data\Application\ApplicationStoreData;
use App\Data\Menu\MenuSetupData;
use App\Data\Menu\MenuStoreData;
use App\Data\MenuItem\MenuItemStoreData;
use App\Enums\MenuItemType;
use App\Enums\MenuSize;
use App\Http\Resources\Menu\MenuShowResource;
use App\Logics\Application\ApplicationStoreLogic;
use App\Logics\MenuItem\MenuItemStoreLogic;
use App\Models\Application;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Role;
use AxoloteSource\Logics\Logics\StoreLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Data;

class MenuSetupLogic extends StoreLogic
{
    public Model|Menu $model;

    public MenuSetupData|Data $input;

    public function __construct(Menu $model)
    {
        parent::__construct($model);
    }

    public function run(MenuSetupData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    public function action(): self
    {
        $application = $this->createApplication();

        if ($this->input->force) {
            $this->purgeExistingMenu($application);
        }

        if ($this->hasErrors()) {
            return $this;
        }

        $this->model = $this->createMenu($application);

        if ($this->hasErrors()) {
            return $this;
        }

        $this->createMenuItems($this->model, $this->input->menu_items, inheritRoles: $this->input->force);

        $this->model->load(['application', 'items.children']);

        $this->response = collect($this->model->toArray());

        return $this;
    }

    protected function withResource(): MenuShowResource
    {
        return new MenuShowResource($this->model);
    }

    /**
     * Elimina de forma definitiva el menú y sus ítems para volver a crearlos
     * desde la configuración. Se usa `forceDelete` porque el slug de los ítems
     * es único a nivel global y las filas con soft delete seguirían ocupándolo.
     */
    private function purgeExistingMenu(Application $application): void
    {
        $existing = Menu::withTrashed()->where('slug', $this->input->name)->first();

        if (! $existing) {
            return;
        }

        if ($existing->application_id !== $application->id) {
            $this->error(__('The menu slug is already registered for another application.'));

            return;
        }

        $itemIds = MenuItem::withTrashed()->where('menu_id', $existing->id)->pluck('id');

        DB::table('menu_item_role')->whereIn('menu_item_id', $itemIds)->delete();

        MenuItem::withTrashed()->where('menu_id', $existing->id)->forceDelete();

        $existing->forceDelete();
    }

    protected function after(): bool
    {
        return ! $this->hasErrors();
    }

    private function createApplication(): Application
    {
        $existing = Application::findBySlug($this->input->application->slug);

        if ($existing) {
            return $existing;
        }

        $applicationData = new ApplicationStoreData(
            name: $this->input->application->name,
            slug: $this->input->application->slug,
            description: $this->input->application->description,
        );

        $logic = app(ApplicationStoreLogic::class);
        $logic->lazyRun($applicationData);

        return $logic->model;
    }

    private function createMenu(Application $application): Menu
    {
        $existing = Menu::findBySlugAndApplication($this->input->name);

        if ($existing && $existing->application_id !== $application->id) {
            $this->error(__('The menu slug is already registered for another application.'));

            return $existing;
        }

        if ($existing) {
            if ($this->input->size !== null && $existing->size !== $this->input->size) {
                $existing->update(['size' => $this->input->size]);
            }

            return $existing;
        }

        $menuData = new MenuStoreData(
            application_id: $application->id,
            name: $this->input->name,
            slug: $this->input->name,
            icon: null,
            size: $this->input->size ?? MenuSize::Md,
            sort_order: 0,
            active: true,
        );

        $logic = app(MenuStoreLogic::class);
        $logic->lazyRun($menuData);

        return $logic->model;
    }

    private function createMenuItems(Menu $menu, array $items, ?string $parentId = null, array $inheritedRoles = [], bool $inheritRoles = false): void
    {
        $sortOrder = 0;

        foreach ($items as $key => $item) {
            $hasChildren = isset($item['menu_items']) && is_array($item['menu_items']);
            $name = $item['name'] ?? $key;
            $type = $hasChildren ? MenuItemType::Header : MenuItemType::Link;
            $roles = ! empty($item['roles']) ? $item['roles'] : ($inheritRoles ? $inheritedRoles : []);

            if (isset($item['type'])) {
                $resolvedType = is_string($item['type']) ? MenuItemType::tryFrom($item['type']) : null;

                if ($resolvedType === null) {
                    $this->error(__('The menu item type ":type" is invalid for ":slug".', [
                        'type' => is_scalar($item['type']) ? (string) $item['type'] : gettype($item['type']),
                        'slug' => $key,
                    ]));

                    return;
                }

                $type = $resolvedType;
            }

            $existingBySlug = MenuItem::where('slug', $key)->first();

            if ($existingBySlug && ($existingBySlug->menu_id !== $menu->id || $existingBySlug->parent_id !== $parentId)) {
                logger('debug', [$existingBySlug, $parentId, $existingBySlug->menu_id, $menu->id, $existingBySlug->parent_id, $parentId]);

                $this->error(__('The menu item slug ":slug" is already registered for another menu or parent.', ['slug' => $key]));

                return;
            }

            $existing = MenuItem::findBySlugAndMenu($key, $menu->id, $parentId);

            if ($existing) {
                if ($existing->type !== $type) {
                    $existing->update(['type' => $type]);
                }

                if ($hasChildren) {
                    $this->createMenuItems($menu, $item['menu_items'], $existing->id, $roles, $inheritRoles);
                }

                continue;
            }

            $menuItemData = new MenuItemStoreData(
                menu_id: $menu->id,
                parent_id: $parentId,
                type: $type,
                name: $name,
                slug: $key,
                route: $item['route'] ?? null,
                path: $item['path'] ?? null,
                icon: $item['icon'] ?? null,
                sort_order: $sortOrder++,
                active: true,
            );

            $logic = app(MenuItemStoreLogic::class);
            $logic->lazyRun($menuItemData);

            if (! empty($roles)) {
                $roleIds = Role::whereIn('key', $roles)->pluck('id');
                $logic->model->roles()->syncWithoutDetaching($roleIds);
            }

            if ($hasChildren) {
                $this->createMenuItems($menu, $item['menu_items'], $logic->model->id, $roles, $inheritRoles);
            }
        }
    }
}
