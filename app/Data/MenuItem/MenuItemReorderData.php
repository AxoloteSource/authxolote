<?php

namespace App\Data\MenuItem;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class MenuItemReorderData extends Data
{
    /**
     * @param  MenuItemReorderItemData[]  $items
     */
    public function __construct(
        #[Rule('required|uuid|exists:menus,id')]
        public string $menu_id,
        /** @var MenuItemReorderItemData[] */
        #[DataCollectionOf(MenuItemReorderItemData::class)]
        #[Rule('required|array|min:1')]
        public array $items,
    ) {}
}
