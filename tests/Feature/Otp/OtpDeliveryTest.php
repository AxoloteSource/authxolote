<?php

namespace Tests\Feature\Otp;

use App\Contracts\OtpSender;
use App\Delivery\FakeOtpSender;
use App\Delivery\MessagesOtpSender;
use App\Enums\OtpChannel;
use App\Enums\OtpDeliveryStatus;
use App\Enums\OtpPurpose;
use App\Jobs\SendOtpJob;
use App\Models\OtpChallenge;
use App\Models\OtpDelivery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class OtpDeliveryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_fake_otp_sender_is_resolved_in_testing(): void
    {
        $sender = app(OtpSender::class);

        $this->assertInstanceOf(FakeOtpSender::class, $sender);

        $provider = $sender->send(OtpChannel::Email, 'user@example.com', '123456', OtpPurpose::Login);

        $this->assertEquals('fake', $provider);
        $this->assertCount(1, $sender->sent);
        $this->assertEquals('123456', $sender->sent[0]['code']);
    }

    public function test_send_otp_job_uses_otp_sender_and_marks_delivery_sent(): void
    {
        $challenge = OtpChallenge::factory()->create();
        $delivery = OtpDelivery::factory()->create(['otp_challenge_id' => $challenge->id]);
        $sender = new FakeOtpSender;

        $job = new SendOtpJob($challenge, $delivery, '123456');
        $job->handle($sender);

        $delivery->refresh();

        $this->assertEquals(OtpDeliveryStatus::Sent, $delivery->status);
        $this->assertNotNull($delivery->sent_at);
        $this->assertEquals('fake', $delivery->provider);
        $this->assertCount(1, $sender->sent);
    }

    public function test_delivery_is_marked_failed_when_sender_fails(): void
    {
        $challenge = OtpChallenge::factory()->create();
        $delivery = OtpDelivery::factory()->create(['otp_challenge_id' => $challenge->id]);

        $sender = new class implements OtpSender
        {
            public function send(OtpChannel $channel, string $destination, string $code, OtpPurpose $purpose): ?string
            {
                throw new RuntimeException('Provider unavailable');
            }
        };

        $job = new SendOtpJob($challenge, $delivery, '123456');

        try {
            $job->handle($sender);
            $this->fail('Expected the sender to throw.');
        } catch (RuntimeException) {
            // expected
        }

        $delivery->refresh();

        $this->assertEquals(OtpDeliveryStatus::Failed, $delivery->status);
        $this->assertNotNull($delivery->failed_at);
        $this->assertStringContainsString('Provider unavailable', $delivery->error);
    }

    public function test_messages_sender_posts_the_expected_payload(): void
    {
        config([
            'services.messages.base_url' => 'https://messages.test',
            'services.messages.endpoint' => '/messages',
            'services.messages.token' => 'secret-token',
        ]);

        Http::fake([
            '*' => Http::response(['id' => 'msg_123'], 200),
        ]);

        $sender = app(MessagesOtpSender::class);

        $provider = $sender->send(OtpChannel::Email, 'user@example.com', '483921', OtpPurpose::Login);

        $this->assertEquals('msg_123', $provider);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://messages.test/messages'
                && $request->hasHeader('Authorization', 'Bearer secret-token')
                && $request['channel'] === 'email'
                && $request['to'] === 'user@example.com'
                && str_contains((string) $request['message'], '483921');
        });
    }

    public function test_messages_sender_throws_when_not_configured(): void
    {
        config(['services.messages.base_url' => null]);

        $this->expectException(\App\Exceptions\Otp\OtpException::class);

        app(MessagesOtpSender::class)->send(OtpChannel::Whatsapp, '+5213121234567', '483921', OtpPurpose::Login);
    }
}
