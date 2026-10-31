<?php

namespace App\Rules;

use App\Enums\OtpChannel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class OtpDestinationRule implements ValidationRule
{
    public function __construct(protected ?OtpChannel $channel) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if ($this->channel === OtpChannel::Email && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $fail(__('The :attribute must be a valid email address.'));

            return;
        }

        if ($this->channel === OtpChannel::Whatsapp && ! preg_match('/^\+[1-9]\d{6,14}$/', (string) $value)) {
            $fail(__('The :attribute must be a valid phone number in E.164 format.'));
        }
    }
}
