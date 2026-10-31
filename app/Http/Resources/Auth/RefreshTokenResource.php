<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array<string, mixed> $resource
 */
class RefreshTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->resource['access_token'],
            'refresh_token' => $this->resource['refresh_token'],
            'token_type' => $this->resource['token_type'] ?? 'Bearer',
            'expires_in' => $this->resource['expires_in'],
        ];
    }
}
