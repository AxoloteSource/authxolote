<?php

namespace App\Delivery;

use App\Contracts\OtpSender;
use App\Enums\OtpChannel;
use App\Enums\OtpPurpose;
use App\Exceptions\Otp\OtpException;
use App\Services\OtpMessage;
use Illuminate\Support\Facades\Http;

class MessagesOtpSender implements OtpSender
{
    public function __construct(protected OtpMessage $otpMessage) {}

    public function send(
        OtpChannel $channel,
        string $destination,
        string $code,
        OtpPurpose $purpose,
    ): ?string {
        $config = config('services.messages');

        if (empty($config['base_url'])) {
            throw OtpException::deliveryNotConfigured();
        }

        $url = rtrim($config['base_url'], '/').'/'.ltrim($config['endpoint'] ?? '/messages', '/');

        $response = Http::withToken($config['token'])
            ->timeout((int) ($config['timeout'] ?? 10))
            ->acceptJson()
            ->post($url, $this->otpMessage->build($channel, $purpose, $code) + [
                'to' => $destination,
            ])
            ->throw();

        return data_get($response->json(), 'id')
            ?? data_get($response->json(), 'data.id')
            ?? 'messages';
    }
}
