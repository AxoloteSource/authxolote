<?php

namespace App\Data\Menu;

use App\Enums\MenuSize;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class MenuSetupData extends Data
{
    public function __construct(
        #[Rule('required|string|max:255')]
        public string $name,

        #[Rule('required|array')]
        public ApplicationInputData $application,

        #[Rule('required|array')]
        public array $menu_items,

        #[Rule('sometimes')]
        public ?MenuSize $size = null,

        #[Rule('sometimes|boolean')]
        public bool $force = false,
    ) {}
}
