<?php

namespace App\Rules;

use App\Models\Setting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Validation\Rules\Password;

class AuthPasswordRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $min = Setting::getPasswordMinLength();
        $letters = Setting::getPasswordRequireLetters();
        $numbers = Setting::getPasswordRequireNumbers();
        $symbols = Setting::getPasswordRequireSymbols();
        $mixedCase = Setting::getPasswordRequireMixedCase();

        $passwordRule = Password::min($min);

        if ($letters) {
            $passwordRule->letters();
        }

        if ($numbers) {
            $passwordRule->numbers();
        }

        if ($symbols) {
            $passwordRule->symbols();
        }

        if ($mixedCase) {
            $passwordRule->mixedCase();
        }

        $validator = validator([$attribute => $value], [$attribute => $passwordRule]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $fail($message);
            }
        }
    }
}
