<?php

namespace App\Traits;

use App\Models\Otp;

trait HasOtpResponse
{
    /**
     * Set the OTP response.
     */
    protected function setOtpResponse(Otp $otp): void
    {
        $this->setResponse([
            'token' => $otp->token,
            'expires_at' => $otp->expires_at,
        ]);
    }
}
