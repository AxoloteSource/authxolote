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
 * Gestión de retos OTP (One-Time Password).
 *
 * Endpoints públicos para generar y verificar códigos OTP enviados por correo
 * electrónico o WhatsApp.
 *
 * @group OTP
 */
class OtpChallengeController extends Controller
{
    /**
     * Crear un reto OTP.
     *
     * Genera un código OTP, invalida los retos activos previos del mismo destino
     * y propósito, y lo envía por el canal indicado. El código nunca se devuelve
     * en la respuesta; solo se expone el identificador del reto y sus tiempos.
     *
     * @unauthenticated
     *
     * @bodyParam purpose string required Propósito del código. Valores permitidos: login, verify_email, verify_phone, password_reset, two_factor, sensitive_action, change_email, change_phone. Example: login
     * @bodyParam channel string required Canal de envío del código. Valores permitidos: email, whatsapp. Example: email
     * @bodyParam destination string required Destino del código. Si el canal es email debe ser un correo válido; si es whatsapp debe ser un teléfono con formato internacional. Example: usuario@example.com
     *
     * @response status=201 scenario="Reto creado" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "challenge_id": "0195f51e-4a37-7087-b5f7-57d001537ab6",
     *     "expires_in": 300,
     *     "resend_in": 60,
     *     "channel": "email"
     *   }
     * }
     * @response status=422 scenario="Error de validación" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "destination": [
     *       "The destination field is required."
     *     ]
     *   }
     * }
     * @response status=422 scenario="Espera entre envíos" {
     *   "status": "error",
     *   "message": "Please wait 45 seconds before requesting a new OTP.",
     *   "data": null
     * }
     * @response status=422 scenario="Límite de solicitudes excedido" {
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
     * Verificar un reto OTP.
     *
     * Valida el código recibido contra el reto indicado. Cada intento fallido
     * incrementa el contador de intentos y el reto se bloquea al alcanzar el
     * máximo configurado. Un código correcto solo puede utilizarse una vez.
     *
     * @unauthenticated
     *
     * @urlParam challenge string required Identificador UUID del reto OTP. Example: 0195f51e-4a37-7087-b5f7-57d001537ab6
     *
     * @bodyParam code string required Código OTP recibido. La longitud debe coincidir con la configuración del sistema (por defecto 6 dígitos). Example: 123456
     *
     * @response status=200 scenario="Código verificado" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "verified": true
     *   }
     * }
     * @response status=422 scenario="Código inválido, expirado, ya usado o máximo de intentos alcanzado" {
     *   "status": "error",
     *   "message": "Invalid OTP code.",
     *   "data": null
     * }
     * @response status=422 scenario="Error de validación" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "code": [
     *       "The code field is required."
     *     ]
     *   }
     * }
     * @response status=404 scenario="Reto no encontrado" {
     *   "message": "No query results for model [App\\Models\\OtpChallenge] 0195f51e-4a37-7087-b5f7-57d001537ab6"
     * }
     */
    public function verify(OtpChallenge $challenge, VerifyOtpChallengeData $data, VerifyOtpChallengeLogic $logic): JsonResponse
    {
        return $logic->run($data, $challenge);
    }
}
