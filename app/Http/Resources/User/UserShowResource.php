<?php

namespace App\Http\Resources\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role_id' => $this->role_id,
            'role' => $this->whenLoaded('role', fn () => [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ]),
            'user_type_id' => $this->user_type_id,
            'user_type' => $this->whenLoaded('userType', fn () => [
                'id' => $this->userType->id,
                'name' => $this->userType->name,
                'key' => $this->userType->key,
            ]),
            'email_verified_at' => $this->email_verified_at,
            'banned_at' => $this->banned_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
