<?php

namespace App\Http\Resources\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class LoginResource extends JsonResource
{
    /**
     * @param  array<string, mixed>  $tokens
     */
    public function __construct(User $resource, private array $tokens)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => $this->id,
                'role_id' => $this->role_id,
                'name' => $this->name,
                'email' => $this->email,
                'email_verified_at' => $this->email_verified_at,
                'remember_token' => $this->remember_token,
                'deleted_at' => $this->deleted_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'role' => $this->role,
            ],
            'access_token' => $this->tokens['access_token'] ?? null,
            'refresh_token' => $this->tokens['refresh_token'] ?? null,
            'token_type' => $this->tokens['token_type'] ?? 'Bearer',
            'expires_in' => $this->tokens['expires_in'] ?? null,
        ];
    }
}
