<?php

namespace App\Exceptions\Otp;

use RuntimeException;

class OtpException extends RuntimeException
{
    public static function cooldown(int $seconds): self
    {
        return new self(__('Please wait :seconds seconds before requesting a new OTP.', ['seconds' => $seconds]));
    }

    public static function rateLimited(): self
    {
        return new self(__('Too many OTP requests. Please try again later.'));
    }

    public static function invalid(): self
    {
        return new self(__('Invalid OTP code.'));
    }

    public static function expired(): self
    {
        return new self(__('This OTP has expired.'));
    }

    public static function alreadyUsed(): self
    {
        return new self(__('This OTP has already been used.'));
    }

    public static function maxAttempts(): self
    {
        return new self(__('Too many attempts. Please request a new OTP.'));
    }

    public static function notFound(): self
    {
        return new self(__('Invalid token.'));
    }

    public static function deliveryNotConfigured(): self
    {
        return new self(__('The messages service is not configured.'));
    }

    public static function fromReason(string $reason): self
    {
        return match ($reason) {
            'already_used' => self::alreadyUsed(),
            'expired' => self::expired(),
            'max_attempts' => self::maxAttempts(),
            'invalid' => self::invalid(),
            default => self::notFound(),
        };
    }
}
