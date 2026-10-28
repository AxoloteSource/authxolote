<?php

namespace App\Http\Controllers\V1\Auth;

use App\Data\Auth\ChangePasswordData;
use App\Data\Auth\IsAllowedData;
use App\Data\Auth\LoginData;
use App\Data\Auth\OtpResetPasswordData;
use App\Data\Auth\RecoveryPasswordData;
use App\Data\Auth\RegisterData;
use App\Data\Auth\ValidateCredentialsData;
use App\Http\Controllers\Controller;
use App\Logics\Auth\ChangePasswordLogic;
use App\Logics\Auth\IsAllowedLogic;
use App\Logics\Auth\LoginShowLogic;
use App\Logics\Auth\LogoutLogic;
use App\Logics\Auth\MeLogic;
use App\Logics\Auth\OtpResetPasswordLogic;
use App\Logics\Auth\RecoveryPasswordLogic;
use App\Logics\Auth\RegisterStoreLogic;
use App\Logics\Auth\ValidateCredentialsLogic;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function register(RegisterData $data, RegisterStoreLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    public function login(LoginData $data, LoginShowLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    public function validateCredentials(ValidateCredentialsData $data, ValidateCredentialsLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    public function logout(LogoutLogic $logic): JsonResponse
    {
        return $logic->run();
    }

    public function isAllowed(IsAllowedData $data, IsAllowedLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    public function me(MeLogic $logic): JsonResponse
    {
        return $logic->run();
    }

    public function recoveryPassword(RecoveryPasswordData $data, RecoveryPasswordLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    public function changePassword(ChangePasswordData $data, ChangePasswordLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    public function resetPassword(OtpResetPasswordData $data, OtpResetPasswordLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
