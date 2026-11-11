<?php

namespace App\Logics\MenuItem;

use App\Data\MenuItem\MenuItemReorderData;
use App\Data\MenuItem\MenuItemReorderItemData;
use App\Models\MenuItem;
use AxoloteSource\Logics\Logics\Logic;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class MenuItemReorderLogic extends Logic
{
    public MenuItemReorderData|Data $input;

    public function __construct(MenuItem $model)
    {
        $this->model = $model;
    }

    public function run(MenuItemReorderData|Data $input): JsonResponse
    {
        return $this->logic($input);
    }

    protected function before(): bool
    {
        return true;
    }

    protected function action(): Logic
    {
        $menuItems = $this->model->newQuery()
            ->where('menu_id', $this->input->menu_id)
            ->get()
            ->keyBy('id');

        /** @var Collection<string, MenuItemReorderItemData> $payload */
        $payload = collect($this->input->items)->keyBy('id');

        if ($payload->count() !== count($this->input->items)) {
            $this->error(__('The menu items payload contains duplicate identifiers.'));

            return $this;
        }

        if ($payload->count() !== $menuItems->count()) {
            $this->error(__('The payload must include every menu item of the menu.'));

            return $this;
        }

        foreach ($payload as $id => $item) {
            if (! $menuItems->has($id)) {
                $this->error(__('The menu item does not belong to the given menu.'));

                return $this;
            }

            if ($item->parent_id !== null && ! $payload->has($item->parent_id)) {
                $this->error(__('The parent menu item does not belong to the given menu.'));

                return $this;
            }

            if ($item->parent_id === $id) {
                $this->error(__('A menu item cannot be its own parent.'));

                return $this;
            }
        }

        if (! $this->hasValidHierarchy($payload)) {
            return $this;
        }

        $updates = $this->normalizedUpdates($payload);

        foreach ($updates as $id => $values) {
            $menuItem = $menuItems->get($id);

            if (! $menuItem instanceof MenuItem) {
                continue;
            }

            if ($menuItem->parent_id === $values['parent_id'] && $menuItem->sort_order === $values['sort_order']) {
                continue;
            }

            $menuItem->update($values);
        }

        $this->setResponse(['reordered' => count($updates)]);

        return $this;
    }

    protected function after(): bool
    {
        return ! $this->hasErrors();
    }

    /**
     * @param  Collection<string, MenuItemReorderItemData>  $payload
     */
    private function hasValidHierarchy(Collection $payload): bool
    {
        foreach ($payload as $id => $item) {
            $visited = [];
            $current = $item->parent_id;

            while ($current !== null) {
                if ($current === $id || isset($visited[$current])) {
                    $this->error(__('The menu item hierarchy contains a cycle.'));

                    return false;
                }

                $visited[$current] = true;

                /** @var MenuItemReorderItemData|null $parent */
                $parent = $payload->get($current);

                if ($parent === null) {
                    $this->error(__('The parent menu item does not belong to the given menu.'));

                    return false;
                }

                $current = $parent->parent_id;
            }
        }

        return true;
    }

    /**
     * @param  Collection<string, MenuItemReorderItemData>  $payload
     * @return array<string, array{parent_id: string|null, sort_order: int}>
     */
    private function normalizedUpdates(Collection $payload): array
    {
        $groups = $payload->groupBy(
            fn (MenuItemReorderItemData $item): string => $item->parent_id ?? '__root__'
        );

        $updates = [];

        foreach ($groups as $group) {
            $ordered = $group
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['id', 'asc'],
                ])
                ->values();

            foreach ($ordered as $index => $item) {
                $updates[$item->id] = [
                    'parent_id' => $item->parent_id,
                    'sort_order' => $index,
                ];
            }
        }

        return $updates;
    }
}
