<?php

namespace App\Services;

use App\Enums\OtpChannel;
use App\Enums\OtpPurpose;
use App\Models\Setting;

class OtpMessage
{
    /**
     * Build the message payload for the given channel and purpose.
     *
     * The returned array intentionally contains no OTP routing metadata beyond
     * what the Messages service needs to deliver a single message.
     *
     * @return array{channel: string, message: string, subject?: string}
     */
    public function build(OtpChannel $channel, OtpPurpose $purpose, string $code): array
    {
        $payload = [
            'channel' => $channel->value,
            'message' => $this->body($purpose, $code),
        ];

        if ($channel->isEmail()) {
            $payload['subject'] = $this->subject($purpose);
        }

        return $payload;
    }

    public function subject(OtpPurpose $purpose): string
    {
        return __('Your verification code');
    }

    public function body(OtpPurpose $purpose, string $code): string
    {
        return __('Your :purpose verification code is :code. It expires in :minutes minutes. Do not share it with anyone.', [
            'purpose' => $purpose->label(),
            'code' => $code,
            'minutes' => (int) ceil(Setting::getOtpChallengeExpirationSeconds() / 60),
        ]);
    }
}
