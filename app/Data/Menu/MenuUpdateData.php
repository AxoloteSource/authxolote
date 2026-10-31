<?php

namespace App\Data\Menu;

use App\Enums\MenuSize;
use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class MenuUpdateData extends Data
{
    public function __construct(
        #[FromRouteParameter('id')]
        public string $id,
        #[Rule('required|string')]
        public string $application_id,
        #[Rule('required|string|max:255')]
        public string $name,
        #[Rule('required|string|max:255')]
        public string $slug,
        #[Rule('nullable|string')]
        public ?string $icon,
        #[Rule('sometimes')]
        public MenuSize $size = MenuSize::Md,
        #[Rule('integer')]
        public int $sort_order = 0,
        #[Rule('boolean')]
        public bool $active = true,
    ) {}
}
