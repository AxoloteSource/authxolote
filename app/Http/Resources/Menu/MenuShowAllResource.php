<?php

namespace App\Http\Resources\Menu;

use App\Http\Resources\MenuItem\MenuItemShowAllResource;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Menu
 */
class MenuShowAllResource extends JsonResource
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
            'items' => $this->whenLoaded('items', fn () => MenuItemShowAllResource::collection(
                $this->items->values()
            )->resolve()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
