<?php

namespace App\Contracts;

use App\Enums\OtpChannel;
use App\Enums\OtpPurpose;

interface OtpSender
{
    /**
     * Deliver an OTP code through the given channel.
     *
     * @return string|null The provider message identifier when available.
     */
    public function send(
        OtpChannel $channel,
        string $destination,
        string $code,
        OtpPurpose $purpose,
    ): ?string;
}
