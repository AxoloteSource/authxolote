<?php

namespace App\Http\Controllers\V1\Otp;

use App\Data\Otp\CreateOtpChallengeData;
use App\Data\Otp\VerifyOtpChallengeData;
use App\Http\Controllers\Controller;
use App\Logics\Otp\CreateOtpChallengeLogic;
use App\Logics\Otp\VerifyOtpChallengeLogic;
use App\Models\OtpChallenge;
use Illuminate\Http\JsonResponse;

/**
 * One-time password (OTP) challenges.
 *
 * Public endpoints used to generate and verify OTP codes delivered by email or
 * WhatsApp.
 *
 * @group OTP
 */
class OtpChallengeController extends Controller
{
    /**
     * Create an OTP challenge.
     *
     * Generates an OTP code, invalidates previous active challenges for the
     * same destination and purpose, and delivers the code through the selected
     * channel. The code is never returned in the response; only the challenge
     * identifier and its timings are exposed.
     *
     * @unauthenticated
     *
     * @bodyParam purpose string required Purpose of the code. Allowed values: login, verify_email, verify_phone, password_reset, two_factor, sensitive_action, change_email, change_phone. Example: login
     * @bodyParam channel string required Delivery channel of the code. Allowed values: email, whatsapp. Example: email
     * @bodyParam destination string required Destination of the code. When the channel is email it must be a valid email address; when it is whatsapp it must be a phone number in international format. Example: jane.doe@example.com
     *
     * @response status=201 scenario="Challenge created" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "challenge_id": "0195f51e-4a37-7087-b5f7-57d001537ab6",
     *     "expires_in": 300,
     *     "resend_in": 60,
     *     "channel": "email"
     *   }
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "destination": [
     *       "The destination field is required."
     *     ]
     *   }
     * }
     * @response status=422 scenario="Cooldown between deliveries" {
     *   "status": "error",
     *   "message": "Please wait 45 seconds before requesting a new OTP.",
     *   "data": null
     * }
     * @response status=422 scenario="Request limit exceeded" {
     *   "status": "error",
     *   "message": "Too many OTP requests. Please try again later.",
     *   "data": null
     * }
     */
    public function store(CreateOtpChallengeData $data, CreateOtpChallengeLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Verify an OTP challenge.
     *
     * Validates the received code against the given challenge. Every failed
     * attempt increments the attempt counter and the challenge is locked once
     * the configured maximum is reached. A correct code can only be used once.
     *
     * @unauthenticated
     *
     * @urlParam challenge_id string required UUID of the OTP challenge. Example: 0195f51e-4a37-7087-b5f7-57d001537ab6
     *
     * @bodyParam code string required OTP code received by the user. Its length must match the system configuration (6 digits by default). Example: 123456
     *
     * @response status=200 scenario="Code verified" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "verified": true
     *   }
     * }
     * @response status=422 scenario="Invalid, expired, already used code, or maximum attempts reached" {
     *   "status": "error",
     *   "message": "Invalid OTP code.",
     *   "data": null
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "code": [
     *       "The code field is required."
     *     ]
     *   }
     * }
     * @response status=404 scenario="Challenge not found" {
     *   "message": "No query results for model [App\\Models\\OtpChallenge] 0195f51e-4a37-7087-b5f7-57d001537ab6"
     * }
     */
    public function verify(OtpChallenge $challenge, VerifyOtpChallengeData $data, VerifyOtpChallengeLogic $logic): JsonResponse
    {
        return $logic->run($data, $challenge);
    }
}
