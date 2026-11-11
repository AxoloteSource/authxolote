<?php

namespace App\Data\Auth;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class RefreshTokenData extends Data
{
    public function __construct(
        #[Rule(['required', 'string'])]
        public string $refresh_token,
    ) {}
}
