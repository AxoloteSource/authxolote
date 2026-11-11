<?php

namespace Database\Factories;

use App\Enums\OtpChannel;
use App\Enums\OtpDeliveryStatus;
use App\Models\OtpChallenge;
use App\Models\OtpDelivery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OtpDelivery>
 */
class OtpDeliveryFactory extends Factory
{
    protected $model = OtpDelivery::class;

    public function definition(): array
    {
        return [
            'otp_challenge_id' => OtpChallenge::factory(),
            'channel' => OtpChannel::Email->value,
            'destination' => $this->faker->unique()->safeEmail(),
            'status' => OtpDeliveryStatus::Pending->value,
            'provider' => null,
            'sent_at' => null,
            'failed_at' => null,
            'error' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OtpDeliveryStatus::Sent->value,
            'provider' => 'messages',
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OtpDeliveryStatus::Failed->value,
            'failed_at' => now(),
            'error' => 'Delivery provider error',
        ]);
    }
}
