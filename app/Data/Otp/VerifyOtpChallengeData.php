<?php

namespace App\Data\Otp;

use App\Models\Setting;
use Spatie\LaravelData\Data;

class VerifyOtpChallengeData extends Data
{
    public function __construct(
        public string $code,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        $digits = Setting::getOtpCodeDigits();

        return [
            'code' => ['required', 'string', 'regex:/^\d{'.$digits.'}$/'],
        ];
    }
}
