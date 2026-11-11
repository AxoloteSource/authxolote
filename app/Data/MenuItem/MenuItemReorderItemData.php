<?php

namespace App\Data\MenuItem;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class MenuItemReorderItemData extends Data
{
    public function __construct(
        #[Rule('required|uuid|exists:menu_items,id')]
        public string $id,
        #[Rule('nullable|uuid')]
        public ?string $parent_id,
        #[Rule('required|integer|min:0')]
        public int $sort_order,
    ) {}
}
