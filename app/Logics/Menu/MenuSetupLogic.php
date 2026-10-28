<?php

namespace App\Logics\Menu;

use App\Data\Menu\MenuSetupData;
use App\Enums\MenuItemType;
use App\Http\Resources\Menu\MenuShowResource;
use App\Models\Application;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Role;
use AxoloteSource\Logics\Logics\StoreLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
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
        $this->model = $this->createMenu($application);

        if ($this->hasErrors()) {
            return $this;
        }

        $this->createMenuItems($this->model, $this->input->menu_items);

        $this->model->load(['application', 'items.children']);

        $this->response = collect($this->model->toArray());

        return $this;
    }

    protected function withResource(): MenuShowResource
    {
        return new MenuShowResource($this->model);
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

        $applicationData = new \App\Data\Application\ApplicationStoreData(
            name: $this->input->application->name,
            slug: $this->input->application->slug,
            description: $this->input->application->description,
        );

        $logic = app(\App\Logics\Application\ApplicationStoreLogic::class);
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
            return $existing;
        }

        $menuData = new \App\Data\Menu\MenuStoreData(
            application_id: $application->id,
            name: $this->input->name,
            slug: $this->input->name,
            icon: null,
            sort_order: 0,
            active: true,
        );

        $logic = app(\App\Logics\Menu\MenuStoreLogic::class);
        $logic->lazyRun($menuData);

        return $logic->model;
    }

    private function createMenuItems(Menu $menu, array $items, ?string $parentId = null): void
    {
        $sortOrder = 0;

        foreach ($items as $key => $item) {
            $hasChildren = isset($item['menu_items']) && is_array($item['menu_items']);
            $name = $item['name'] ?? $key;
            $type = $hasChildren ? MenuItemType::Header : MenuItemType::Link;

            $existingBySlug = MenuItem::where('slug', $key)->first();

            logger('$existingBySlug', [$existingBySlug]);

            if ($existingBySlug && ($existingBySlug->menu_id !== $menu->id || $existingBySlug->parent_id !== $parentId)) {
                logger('entro aquí');
                logger('debug', [$existingBySlug, $parentId, $existingBySlug->menu_id, $menu->id, $existingBySlug->parent_id, $parentId]);

                $this->error(__('The menu item slug ":slug" is already registered for another menu or parent.', ['slug' => $key]));

                return;
            }

            $existing = MenuItem::findBySlugAndMenu($key, $menu->id, $parentId);

            if ($existing) {
                if ($hasChildren) {
                    $this->createMenuItems($menu, $item['menu_items'], $existing->id);
                }

                continue;
            }

            $menuItemData = new \App\Data\MenuItem\MenuItemStoreData(
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

            $logic = app(\App\Logics\MenuItem\MenuItemStoreLogic::class);
            $logic->lazyRun($menuItemData);

            if (! empty($item['roles'])) {
                $roleIds = Role::whereIn('key', $item['roles'])->pluck('id');
                $logic->model->roles()->syncWithoutDetaching($roleIds);
            }

            if ($hasChildren) {
                $this->createMenuItems($menu, $item['menu_items'], $logic->model->id);
            }
        }
    }
}
