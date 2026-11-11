<?php

namespace App\Logics\Otp;

use App\Data\Otp\CreateOtpChallengeData;
use App\Enums\OtpChannel;
use App\Enums\OtpDeliveryStatus;
use App\Enums\OtpPurpose;
use App\Exceptions\Otp\OtpException;
use App\Http\Resources\Otp\OtpChallengeResource;
use App\Jobs\SendOtpJob;
use App\Models\OtpChallenge;
use App\Models\OtpDelivery;
use App\Models\Setting;
use App\Models\User;
use AxoloteSource\Logics\Logics\StoreLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\LaravelData\Data;

class CreateOtpChallengeLogic extends StoreLogic
{
    protected CreateOtpChallengeData|Data $input;

    public OtpChallenge|Model $model;

    protected OtpDelivery $delivery;

    protected string $code;

    protected OtpPurpose $purpose;

    protected OtpChannel $channel;

    public function __construct(OtpChallenge $model)
    {
        parent::__construct($model);
    }

    public function run(CreateOtpChallengeData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function initializer(): bool
    {
        $this->purpose = OtpPurpose::from($this->input->purpose);
        $this->channel = OtpChannel::from($this->input->channel);

        $this->input->destination = $this->normalizeDestination($this->channel, $this->input->destination);
        $this->input->user_id = $this->resolveUserId($this->channel, $this->input->destination);

        $this->code = $this->generateCode();

        $this->input->code_hash = Hash::make($this->code);
        $this->input->expires_at = now()->addSeconds(Setting::getOtpChallengeExpirationSeconds())->toDateTimeString();
        $this->input->verified_at = null;
        $this->input->attempts = 0;
        $this->input->max_attempts = Setting::getOtpChallengeMaxAttempts();

        return true;
    }

    protected function before(): bool
    {
        try {
            $this->ensureCooldown($this->input->destination, $this->purpose);
            $this->ensureWithinRateLimit($this->input->destination, $this->purpose);
        } catch (OtpException $e) {
            return $this->error($e->getMessage());
        }

        $this->invalidateActiveChallenges($this->input->destination, $this->purpose);

        return true;
    }

    protected function after(): bool
    {
        $this->delivery = $this->model->deliveries()->create([
            'channel' => $this->input->channel,
            'destination' => $this->input->destination,
            'status' => OtpDeliveryStatus::Pending->value,
        ]);

        SendOtpJob::dispatch($this->model, $this->delivery, $this->code);

        return true;
    }

    protected function withResource(): OtpChallengeResource
    {
        return new OtpChallengeResource($this->model);
    }

    private function normalizeDestination(OtpChannel $channel, string $destination): string
    {
        if ($channel->isEmail()) {
            return mb_strtolower(trim($destination));
        }

        return preg_replace('/[^0-9+]/', '', $destination) ?? $destination;
    }

    private function resolveUserId(OtpChannel $channel, string $destination): ?string
    {
        if (! $channel->isEmail()) {
            return null;
        }

        return User::query()
            ->where('email', $destination)
            ->value('id');
    }

    /**
     * @throws OtpException
     */
    private function ensureCooldown(string $destination, OtpPurpose $purpose): void
    {
        $cooldown = Setting::getOtpResendCooldownSeconds();

        if ($cooldown <= 0) {
            return;
        }

        $lastChallenge = OtpChallenge::query()
            ->where('destination', $destination)
            ->where('purpose', $purpose->value)
            ->latest('created_at')
            ->first();

        if (! $lastChallenge) {
            return;
        }

        $availableAt = $lastChallenge->created_at->copy()->addSeconds($cooldown);

        if ($availableAt->isFuture()) {
            throw OtpException::cooldown((int) ceil(now()->floatDiffInSeconds($availableAt)));
        }
    }

    /**
     * @throws OtpException
     */
    private function ensureWithinRateLimit(string $destination, OtpPurpose $purpose): void
    {
        $max = Setting::getOtpRateLimitMax();
        $decay = Setting::getOtpRateLimitDecaySeconds();

        if ($max <= 0) {
            return;
        }

        $keys = [
            'otp:create:destination:'.sha1($destination.'|'.$purpose->value),
            'otp:create:ip:'.sha1((string) request()->ip()),
        ];

        foreach ($keys as $key) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                throw OtpException::rateLimited();
            }
        }

        foreach ($keys as $key) {
            RateLimiter::hit($key, $decay);
        }
    }

    private function invalidateActiveChallenges(string $destination, OtpPurpose $purpose): void
    {
        OtpChallenge::query()
            ->where('destination', $destination)
            ->where('purpose', $purpose->value)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);
    }

    private function generateCode(): string
    {
        $digits = Setting::getOtpCodeDigits();
        $max = (10 ** $digits) - 1;

        return str_pad((string) random_int(0, $max), $digits, '0', STR_PAD_LEFT);
    }
}
