<?php

namespace App\Http\Controllers\V1\Auth;

use App\Data\Auth\ChangePasswordData;
use App\Data\Auth\IsAllowedData;
use App\Data\Auth\LoginData;
use App\Data\Auth\OtpResetPasswordData;
use App\Data\Auth\RecoveryPasswordData;
use App\Data\Auth\RefreshTokenData;
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
use App\Logics\Auth\RefreshTokenLogic;
use App\Logics\Auth\RegisterStoreLogic;
use App\Logics\Auth\ValidateCredentialsLogic;
use Illuminate\Http\JsonResponse;

/**
 * Authentication endpoints: registration, login, logout, password recovery and
 * password change, plus helpers to inspect the authenticated user and their
 * permissions.
 *
 * Tokens are issued by Laravel Passport and must be sent as
 * `Authorization: Bearer {access_token}`.
 *
 * @group Authentication
 */
class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * Creates a user for the given role and immediately returns an access token
     * for that user. Requires an authenticated caller.
     *
     * @authenticated
     *
     * @bodyParam name string required Full name of the user. Example: Jane Doe
     * @bodyParam email string required Unique email address of the user. Example: jane.doe@example.com
     * @bodyParam password string required Password of the user. Minimum length and complexity are defined in the system settings. Example: Password123!
     * @bodyParam password_confirmation string required Confirmation of the password. Must match `password`. Example: Password123!
     * @bodyParam role_key string required Key of the role assigned to the new user. Example: admin
     *
     * @response status=201 scenario="User registered" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "user": {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537ab6",
     *       "role_id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *       "name": "Jane Doe",
     *       "email": "jane.doe@example.com",
     *       "email_verified_at": null,
     *       "remember_token": null,
     *       "deleted_at": null,
     *       "created_at": "2026-01-15T10:00:00.000000Z",
     *       "updated_at": "2026-01-15T10:00:00.000000Z",
     *       "role": {
     *         "id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *         "name": "Admin",
     *         "key": "admin"
     *       }
     *     },
     *     "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
     *     "refresh_token": "def50200a1b2c3d4e5f6...",
     *     "token_type": "Bearer",
     *     "expires_in": 3600
     *   }
     * }
     * @response status=403 scenario="Missing permission" {
     *   "message": "You do not have permission to access this resource",
     *   "data": {
     *     "action": "auth.register"
     *   }
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "email": [
     *       "The email has already been taken."
     *     ]
     *   }
     * }
     */
    public function register(RegisterData $data, RegisterStoreLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Log in.
     *
     * Validates the credentials and returns the user together with a new
     * Passport access token.
     *
     * @unauthenticated
     *
     * @bodyParam email string required Email address of the user. Example: jane.doe@example.com
     * @bodyParam password string required Password of the user. Example: Password123!
     *
     * @response status=200 scenario="Authenticated" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "user": {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537ab6",
     *       "role_id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *       "name": "Jane Doe",
     *       "email": "jane.doe@example.com",
     *       "email_verified_at": null,
     *       "remember_token": null,
     *       "deleted_at": null,
     *       "created_at": "2026-01-15T10:00:00.000000Z",
     *       "updated_at": "2026-01-15T10:00:00.000000Z",
     *       "role": {
     *         "id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *         "name": "Admin",
     *         "key": "admin"
     *       }
     *     },
     *     "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
     *     "refresh_token": "def50200a1b2c3d4e5f6...",
     *     "token_type": "Bearer",
     *     "expires_in": 3600
     *   }
     * }
     * @response status=422 scenario="Invalid credentials" {
     *   "status": "error",
     *   "message": "Invalid credentials",
     *   "data": null
     * }
     */
    public function login(LoginData $data, LoginShowLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Refresh the access token.
     *
     * Exchanges a valid refresh token for a new access token and a new refresh
     * token. The previous refresh token and its access token are revoked.
     *
     * @unauthenticated
     *
     * @bodyParam refresh_token string required Refresh token returned by the login, register or refresh endpoint. Example: def50200a1b2c3d4e5f6...
     *
     * @response status=200 scenario="Token refreshed" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
     *     "refresh_token": "def50200a1b2c3d4e5f6...",
     *     "token_type": "Bearer",
     *     "expires_in": 3600
     *   }
     * }
     * @response status=401 scenario="Invalid refresh token" {
     *   "status": "error",
     *   "message": "Invalid or expired refresh token",
     *   "data": null
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "refresh_token": [
     *       "The refresh token field is required."
     *     ]
     *   }
     * }
     */
    public function refreshToken(RefreshTokenData $data, RefreshTokenLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Validate credentials.
     *
     * Checks whether the given email and password are valid without issuing a
     * token. Requires authentication and ownership of the
     * `auth.validate-credentials` action.
     *
     * @authenticated
     *
     * @bodyParam email string required Email address to validate. Example: jane.doe@example.com
     * @bodyParam password string required Password to validate. Example: Password123!
     *
     * @response status=200 scenario="Credentials checked" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "valid": true
     *   }
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "email": [
     *       "The email field must be a valid email address."
     *     ]
     *   }
     * }
     */
    public function validateCredentials(ValidateCredentialsData $data, ValidateCredentialsLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Log out.
     *
     * Revokes the access token used for the current request.
     *
     * @authenticated
     *
     * @response status=200 scenario="Logged out" {
     *   "status": "OK",
     *   "message": null,
     *   "data": null
     * }
     */
    public function logout(LogoutLogic $logic): JsonResponse
    {
        return $logic->run();
    }

    /**
     * Check actions allowed for the current user.
     *
     * Returns a map of the requested actions with a boolean that indicates
     * whether the authenticated user is allowed to perform each one.
     *
     * @authenticated
     *
     * @bodyParam actions string[] required List of action keys to check. Example: ["auth.users.index", "auth.users.store"]
     *
     * @response status=200 scenario="Actions resolved" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "auth.users.index": true,
     *     "auth.users.store": false
     *   }
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "actions": [
     *       "The actions field is required."
     *     ]
     *   }
     * }
     */
    public function isAllowed(IsAllowedData $data, IsAllowedLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Get the authenticated user.
     *
     * Returns the profile of the user that owns the access token, including its
     * assigned role.
     *
     * @authenticated
     *
     * @response status=200 scenario="Authenticated user" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "user": {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537ab6",
     *       "role_id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *       "name": "Jane Doe",
     *       "email": "jane.doe@example.com",
     *       "email_verified_at": null,
     *       "remember_token": null,
     *       "deleted_at": null,
     *       "created_at": "2026-01-15T10:00:00.000000Z",
     *       "updated_at": "2026-01-15T10:00:00.000000Z",
     *       "role": {
     *         "id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *         "name": "Admin",
     *         "key": "admin"
     *       }
     *     }
     *   }
     * }
     */
    public function me(MeLogic $logic): JsonResponse
    {
        return $logic->run();
    }

    /**
     * Request a password recovery.
     *
     * Generates a recovery token and sends a one-time password (OTP) to the
     * email address related to the user. The response always contains a token,
     * even when the email does not belong to a registered user.
     *
     * @unauthenticated
     *
     * @bodyParam email string required Email address of the account. Example: jane.doe@example.com
     *
     * @response status=201 scenario="Recovery requested" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "token": "7d2f0b1c9a4e6f8d",
     *     "expires_at": "2026-01-15T10:10:00.000000Z"
     *   }
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "email": [
     *       "The email field must be a valid email address."
     *     ]
     *   }
     * }
     */
    public function recoveryPassword(RecoveryPasswordData $data, RecoveryPasswordLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Request a password change.
     *
     * Sends an OTP to the authenticated user and returns a token that must be
     * used together with the OTP in the reset password endpoint.
     *
     * @authenticated
     *
     * @response status=201 scenario="Change requested" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "token": "7d2f0b1c9a4e6f8d",
     *     "expires_at": "2026-01-15T10:10:00.000000Z"
     *   }
     * }
     */
    public function changePassword(ChangePasswordData $data, ChangePasswordLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Reset the password.
     *
     * Validates the recovery token and the OTP code, then stores the new
     * password. A token can only be used once.
     *
     * @unauthenticated
     *
     * @bodyParam token string required Recovery token returned by the recovery or change password endpoint. Example: 7d2f0b1c9a4e6f8d
     * @bodyParam otp_code string required One-time password received by the user. Example: 123456
     * @bodyParam password string required New password. Minimum length and complexity are defined in the system settings. Example: NewPassword123!
     * @bodyParam password_confirmation string required Confirmation of the new password. Must match `password`. Example: NewPassword123!
     *
     * @response status=200 scenario="Password reset" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "message": "Password reset successfully."
     *   }
     * }
     * @response status=422 scenario="Invalid token or OTP" {
     *   "status": "error",
     *   "message": "Invalid OTP code.",
     *   "data": null
     * }
     */
    public function resetPassword(OtpResetPasswordData $data, OtpResetPasswordLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
