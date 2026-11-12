<?php

namespace App\Http\Resources\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class MeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => $this->id,
                'role_id' => $this->role_id,
                'user_type_id' => $this->user_type_id,
                'name' => $this->name,
                'email' => $this->email,
                'email_verified_at' => $this->email_verified_at,
                'remember_token' => $this->remember_token,
                'deleted_at' => $this->deleted_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'role' => $this->role,
                'user_type' => $this->userType,
            ],
        ];
    }
}
