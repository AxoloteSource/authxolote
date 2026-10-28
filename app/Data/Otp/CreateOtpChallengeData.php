<?php

namespace App\Data\Otp;

use App\Enums\OtpChannel;
use App\Enums\OtpPurpose;
use App\Rules\OtpDestinationRule;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CreateOtpChallengeData extends Data
{
    public ?string $user_id = null;

    public ?string $code_hash = null;

    public ?string $expires_at = null;

    public ?string $verified_at = null;

    public ?int $attempts = null;

    public ?int $max_attempts = null;

    public function __construct(
        public string $purpose,
        public string $channel,
        public string $destination,
    ) {}

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(ValidationContext $context): array
    {
        $channel = OtpChannel::tryFrom((string) ($context->payload['channel'] ?? ''));

        return [
            'purpose' => ['required', 'string', Rule::enum(OtpPurpose::class)],
            'channel' => ['required', 'string', Rule::enum(OtpChannel::class)],
            'destination' => ['required', 'string', new OtpDestinationRule($channel)],
            'user_id' => ['prohibited'],
            'code_hash' => ['prohibited'],
            'expires_at' => ['prohibited'],
            'verified_at' => ['prohibited'],
            'attempts' => ['prohibited'],
            'max_attempts' => ['prohibited'],
        ];
    }
}
