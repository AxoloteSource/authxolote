<?php

namespace App\Delivery;

use App\Contracts\OtpSender;
use App\Enums\OtpChannel;
use App\Enums\OtpPurpose;

class FakeOtpSender implements OtpSender
{
    /**
     * @var array<int, array{channel: string, destination: string, code: string, purpose: string}>
     */
    public array $sent = [];

    public function send(
        OtpChannel $channel,
        string $destination,
        string $code,
        OtpPurpose $purpose,
    ): ?string {
        $this->sent[] = [
            'channel' => $channel->value,
            'destination' => $destination,
            'code' => $code,
            'purpose' => $purpose->value,
        ];

        return 'fake';
    }
}
