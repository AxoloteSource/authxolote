<?php

namespace App\Http\Resources\PersonalAccessToken;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Passport\Token;

/**
 * @mixin Token
 */
class PersonalAccessTokenStoreResource extends JsonResource
{
    public function __construct(Token $resource, private string $plainTextToken)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'token' => $this->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $this->expires_at,
            'revoked' => (bool) $this->revoked,
            'created_at' => $this->created_at,
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'user_type_id' => $this->user->user_type_id,
            ] : null),
        ];
    }
}
