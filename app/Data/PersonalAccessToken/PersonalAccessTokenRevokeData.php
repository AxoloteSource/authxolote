<?php

namespace App\Data\PersonalAccessToken;

use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Data;

class PersonalAccessTokenRevokeData extends Data
{
    public function __construct(
        #[FromRouteParameter('id')]
        public string $id,
    ) {}
}
