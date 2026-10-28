<?php

namespace App\Logics\Otp;

use App\Data\Otp\VerifyOtpChallengeData;
use App\Exceptions\Otp\OtpException;
use App\Http\Resources\Otp\OtpVerifyResource;
use App\Models\OtpChallenge;
use AxoloteSource\Logics\Logics\Logic;
use AxoloteSource\Logics\Traits\OnlyWithAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\LaravelData\Data;

class VerifyOtpChallengeLogic extends Logic
{
    use OnlyWithAction;

    protected VerifyOtpChallengeData|Data $input;

    protected OtpChallenge $challenge;

    public function run(VerifyOtpChallengeData|Data $input, OtpChallenge $challenge): JsonResponse
    {
        $this->challenge = $challenge;

        return $this->logic($input);
    }

    protected function before(): bool
    {
        try {
            $this->verify($this->challenge, $this->input->code);
        } catch (OtpException $e) {
            return $this->error($e->getMessage());
        }

        $this->response = collect(['verified' => true]);

        return true;
    }

    protected function action(): self
    {
        return $this;
    }

    protected function withResource(): OtpVerifyResource
    {
        return new OtpVerifyResource($this->response);
    }

    /**
     * @throws OtpException
     */
    private function verify(OtpChallenge $challenge, string $code): void
    {
        $reason = DB::transaction(function () use ($challenge, $code) {
            $locked = OtpChallenge::query()
                ->whereKey($challenge->getKey())
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                return 'not_found';
            }

            if ($locked->isVerified()) {
                return 'already_used';
            }

            if ($locked->isExpired()) {
                return 'expired';
            }

            if ($locked->hasReachedMaxAttempts()) {
                return 'max_attempts';
            }

            if (! Hash::check($code, $locked->code_hash)) {
                $locked->increment('attempts');

                return 'invalid';
            }

            $locked->forceFill(['verified_at' => now()])->save();

            return null;
        });

        if ($reason !== null) {
            throw OtpException::fromReason($reason);
        }
    }
}
