<?php

namespace App\Logics\Auth;

use App\Data\Auth\OtpStoreData;
use App\Models\Otp;
use App\Models\Setting;
use AxoloteSource\Logics\Logics\StoreLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\LaravelData\Data;

class OtpStoreLogic extends StoreLogic
{
    public Model|Otp $model;

    protected Data|OtpStoreData $input;

    protected int $otpLength;

    protected int $otpExpiresInMinutes;

    protected int $otpMaxAttempts;

    protected int $otpRetryAfterSeconds;

    protected ?Otp $lastOtp;

    public function __construct(Otp $model)
    {
        parent::__construct($model);
    }

    public function run(OtpStoreData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function before(): bool
    {
        $this->init();

        if (! $this->validateRateLimit()) {
            return false;
        }

        $this->input->token = Otp::generateToken();
        $this->input->expires_at = now()->addMinutes($this->otpExpiresInMinutes);

        $code = $this->generateOtpCode($this->otpLength);
        $this->input->otp_code = Hash::make($code);

        return true;
    }

    protected function init(): void
    {
        $this->otpLength = max(6, Setting::getOtpCodeDigits());
        $this->otpExpiresInMinutes = Setting::getOtpExpiresInMinutes();
        $this->otpMaxAttempts = Setting::getOtpMaxAttempts();
        $this->otpRetryAfterSeconds = Setting::getOtpRetryAfterSeconds();
        $this->lastOtp = Otp::where('user_id', $this->input->user_id)
            ->latest()
            ->first();
    }

    protected function validateRateLimit(): bool
    {
        if ($this->lastOtp && $this->lastOtp->created_at->addSeconds($this->otpRetryAfterSeconds)->isFuture()) {
            $diff = $this->lastOtp->created_at->addSeconds($this->otpRetryAfterSeconds)->diffInSeconds(now());

            return $this->error(__('Please wait :seconds seconds before requesting a new OTP.', ['seconds' => $diff]));
        }

        $attemptsCount = Otp::where('user_id', $this->input->user_id)
            ->where('created_at', '>', now()->subMinutes(10))
            ->count();

        if ($attemptsCount >= $this->otpMaxAttempts) {
            return $this->error(__('Too many OTP requests. Please try again later.'));
        }

        return true;
    }

    protected function after(): bool
    {
        $this->model->expiredOtherActiveOtps();

        return true;
    }

    private function generateOtpCode(int $length): string
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= (string) random_int(0, 9);
        }

        return $code;
    }
}
