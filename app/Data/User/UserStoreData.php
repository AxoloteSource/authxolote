<?php

namespace App\Data\User;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class UserStoreData extends Data
{
    public function __construct(
        #[Rule('required|string|max:255')]
        public string $name,
        #[Rule('required|email|max:255|unique:users,email')]
        public string $email,
        #[Rule('required|exists:roles,id')]
        public string $role_id,
        #[Rule('required|string|min:8')]
        public string $password,
    ) {}
}
