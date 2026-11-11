<?php

namespace App\Http\Resources\Menu;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Menu
 */
class MenuIndexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'application' => $this->whenLoaded('application', fn () => $this->application?->name),
            'items_count' => $this->items_count,
            'size' => $this->size?->value,
            'active' => $this->active,
            'created_at' => $this->created_at,
        ];
    }
}
