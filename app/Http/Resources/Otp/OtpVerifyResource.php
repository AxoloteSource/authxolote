<?php

namespace App\Http\Resources\Otp;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OtpVerifyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'verified' => (bool) $this['verified'],
        ];
    }
}
