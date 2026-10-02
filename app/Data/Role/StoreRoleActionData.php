<?php

namespace App\Data\Role;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class StoreRoleActionData extends Data
{
    public function __construct(
        #[Rule(['required'])]
        public array|Collection $roles,
        #[Rule(['boolean'])]
        public bool $force = false,
    ) {
        $this->roles = collect($this->roles);
    }
}
