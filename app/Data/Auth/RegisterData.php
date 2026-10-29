<?php

namespace App\Data\Auth;

use App\Models\Role;
use App\Rules\AuthPasswordRule;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class RegisterData extends Data
{
    public function __construct(
        #[Rule('required|string|max:50|min:3')]
        public string $name,
        #[Rule('required|email|string|unique:users')]
        public string $email,
        public string $password,
        #[Rule(['required', 'exists:roles,key'])]
        public string $role_key,
        public ?string $role_id,
    ) {
        $this->role_id = Role::where('key', $this->role_key)->first()->id;
    }

    public static function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', new AuthPasswordRule],
        ];
    }
}
