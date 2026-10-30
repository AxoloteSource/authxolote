<?php

namespace App\Http\Resources\Menu;

use App\Http\Resources\MenuItem\MenuItemShowResource;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Menu
 */
class MenuShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'application' => $this->whenLoaded('application', fn () => $this->application?->name),
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'size' => $this->size?->value,
            'sort_order' => $this->sort_order,
            'active' => $this->active,
            'items' => $this->whenLoaded('items', fn () => MenuItemShowResource::collection(
                $this->items
                    ->filter(fn (MenuItem $item) => $item->isVisibleTo($this->currentRoleId($request)))
                    ->values()
            )->resolve()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function currentRoleId(Request $request): ?string
    {
        return $request->user()?->role_id;
    }
}
