<?php

namespace App\Enums;

enum SettingEnum: string
{
    case PasswordMinLength = 'password_min_length';
    case PasswordRequireLetters = 'password_require_letters';
    case PasswordRequireNumbers = 'password_require_numbers';
    case PasswordRequireSymbols = 'password_require_symbols';
    case PasswordRequireMixedCase = 'password_require_mixed_case';
    case OtpLength = 'otp_length';
    case OtpExpiresInMinutes = 'otp_expires_in_minutes';
    case OtpMaxAttempts = 'otp_max_attempts';
    case OtpRetryAfterSeconds = 'otp_retry_after_seconds';
    case OtpCodeDigits = 'otp_code_digits';
    case OtpChallengeExpirationSeconds = 'otp_challenge_expiration_seconds';
    case OtpChallengeMaxAttempts = 'otp_challenge_max_attempts';
    case OtpResendCooldownSeconds = 'otp_resend_cooldown_seconds';
    case OtpRateLimitMax = 'otp_rate_limit_max';
    case OtpRateLimitDecaySeconds = 'otp_rate_limit_decay_seconds';
    case AccessTokenExpiresInMinutes = 'access_token_expires_in_minutes';
    case RefreshTokenExpiresInMinutes = 'refresh_token_expires_in_minutes';
}
