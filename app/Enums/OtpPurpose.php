<?php

namespace App\Enums;

enum OtpPurpose: string
{
    case Login = 'login';
    case VerifyEmail = 'verify_email';
    case VerifyPhone = 'verify_phone';
    case PasswordReset = 'password_reset';
    case TwoFactor = 'two_factor';
    case SensitiveAction = 'sensitive_action';
    case ChangeEmail = 'change_email';
    case ChangePhone = 'change_phone';

    /**
     * Human readable label used by OTP messages.
     */
    public function label(): string
    {
        return match ($this) {
            self::Login => __('login'),
            self::VerifyEmail => __('email verification'),
            self::VerifyPhone => __('phone verification'),
            self::PasswordReset => __('password reset'),
            self::TwoFactor => __('two factor authentication'),
            self::SensitiveAction => __('sensitive action'),
            self::ChangeEmail => __('email change'),
            self::ChangePhone => __('phone change'),
        };
    }
}
