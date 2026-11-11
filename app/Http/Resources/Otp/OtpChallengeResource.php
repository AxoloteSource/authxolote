<?php

namespace App\Http\Resources\Otp;

use App\Models\OtpChallenge;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OtpChallenge
 */
class OtpChallengeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'challenge_id' => $this->id,
            'expires_in' => max(0, (int) $this->expires_at->diffInSeconds(now())),
            'resend_in' => Setting::getOtpResendCooldownSeconds(),
            'channel' => $this->channel->value,
        ];
    }
}
