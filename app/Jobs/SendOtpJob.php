<?php

namespace App\Jobs;

use App\Contracts\OtpSender;
use App\Models\OtpChallenge;
use App\Models\OtpDelivery;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendOtpJob implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public OtpChallenge $challenge,
        public OtpDelivery $delivery,
        public string $code,
    ) {}

    public function handle(OtpSender $sender): void
    {
        try {
            $provider = $sender->send(
                $this->delivery->channel,
                $this->delivery->destination,
                $this->code,
                $this->challenge->purpose,
            );

            $this->delivery->markAsSent($provider);
        } catch (Throwable $e) {
            $this->delivery->markAsFailed($e->getMessage());

            throw $e;
        }
    }
}
