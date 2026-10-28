<?php

namespace App\Data\Auth;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class ValidateCredentialsData extends Data
{
    public function __construct(
        #[Rule(['required', 'email:rfc', 'string'])]
        public string $email,
        #[Rule(['required', 'string'])]
        public string $password,
    ) {}
}
