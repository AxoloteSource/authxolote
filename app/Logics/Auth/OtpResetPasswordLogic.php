<?php

namespace App\Logics\Auth;

use App\Data\Auth\OtpResetPasswordData;
use App\Models\Otp;
use App\Models\Setting;
use AxoloteSource\Logics\Logics\UpdateLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\LaravelData\Data;

class OtpResetPasswordLogic extends UpdateLogic
{
    protected Data|OtpResetPasswordData $input;

    public Otp|Model $model;

    protected bool $validateNotFound = false;

    public function __construct(Otp $model)
    {
        parent::__construct($model);
    }

    public function run(OtpResetPasswordData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function before(): bool
    {
        // 1. Buscar el OTP por token
        $otp = $this->model->where('token', $this->input->token)->first();

        if (! $otp) {
            return $this->error(__('Invalid token.'));
        }

        $this->model = $otp;

        // 2. Validar si el OTP ya fue usado
        if ($this->model->used_at) {
            return $this->error(__('This OTP has already been used.'));
        }

        // 3. Validar si el OTP ha expirado
        if ($this->model->expires_at->isPast()) {
            return $this->error(__('This OTP has expired.'));
        }

        $maxAttempts = max(1, Setting::getOtpMaxAttempts());

        // 4. Validar si el OTP alcanzó el máximo de intentos
        if ($this->model->attempts >= $maxAttempts) {
            return $this->error(__('Too many OTP attempts. Please request a new code.'));
        }

        // 5. Validar si el otp_code coincide
        if (! Hash::check($this->input->otp_code, $this->model->otp_code)) {
            $this->model->increment('attempts');
            $this->model->refresh();

            if ($this->model->attempts >= $maxAttempts) {
                $this->model->forceFill(['expires_at' => now()])->save();

                return $this->error(__('Too many OTP attempts. Please request a new code.'));
            }

            return $this->error(__('Invalid OTP code.'));
        }

        return true;
    }

    protected function action(): self
    {
        $this->model->forceFill(['used_at' => now()])->save();
        $this->response = collect($this->model);

        return $this;
    }

    protected function after(): bool
    {
        $user = $this->model->user;
        $user->password = $this->input->password;
        $user->save();

        $this->response = collect(['message' => __('Password reset successfully.')]);

        return true;
    }
}
