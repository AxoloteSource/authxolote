<?php

namespace Database\Factories;

use App\Enums\OtpChannel;
use App\Enums\OtpPurpose;
use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<OtpChallenge>
 */
class OtpChallengeFactory extends Factory
{
    protected $model = OtpChallenge::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'purpose' => OtpPurpose::Login->value,
            'channel' => OtpChannel::Email->value,
            'destination' => $this->faker->unique()->safeEmail(),
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
            'verified_at' => null,
            'attempts' => 0,
            'max_attempts' => 5,
        ];
    }

    public function whatsapp(): static
    {
        return $this->state(fn (array $attributes) => [
            'channel' => OtpChannel::Whatsapp->value,
            'destination' => '+5213121234567',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinute(),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now(),
        ]);
    }

    public function maxAttemptsReached(): static
    {
        return $this->state(fn (array $attributes) => [
            'attempts' => $attributes['max_attempts'] ?? 5,
        ]);
    }
}
