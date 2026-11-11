<?php

namespace App\Data\PersonalAccessToken;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class PersonalAccessTokenStoreData extends Data
{
    public function __construct(
        #[Rule(['required', 'uuid', 'exists:users,id'])]
        public string $user_id,
        #[Rule(['required', 'string', 'max:255'])]
        public string $name,
        #[Rule(['nullable', 'integer', 'in:7,30,60,90'])]
        public ?int $expires_in = null,
    ) {}
}
