<?php

namespace App\Data\PersonalAccessToken;

use AxoloteSource\Logics\Data\IndexData;
use Spatie\LaravelData\Attributes\Validation\Rule;

class PersonalAccessTokenIndexData extends IndexData
{
    #[Rule(['nullable', 'uuid', 'exists:users,id'])]
    public ?string $user_id = null;
}
