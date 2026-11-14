<?php

namespace Tests\Feature\Auth;

use App\Data\Auth\OtpStoreData;
use App\Logics\Auth\OtpStoreLogic;
use App\Models\Otp;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OtpStoreLogicTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_create_otp_for_user()
    {
        $user = User::factory()->create();
        $data = new OtpStoreData($user->id);
        $logic = app(OtpStoreLogic::class);

        $response = $logic->run($data);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertDatabaseHas('otps', [
            'user_id' => $user->id,
        ]);

        $otp = Otp::where('user_id', $user->id)->first();
        $this->assertGreaterThanOrEqual(60, strlen($otp->otp_code));

        $otpExpiresInMinutes = (int) Setting::where('name', 'otp_expires_in_minutes')->value('value');
        $expectedExpiration = now()->addMinutes($otpExpiresInMinutes);

        // Verificamos que la expiración sea aproximadamente la esperada (margen de 1 minuto)
        $this->assertTrue($otp->expires_at->diffInMinutes($expectedExpiration) <= 1);
    }

    public function test_only_one_valid_otp_allowed()
    {
        $user = User::factory()->create();
        Setting::where('name', 'otp_retry_after_seconds')->update(['value' => '0']);

        // Primer OTP
        app(OtpStoreLogic::class)->run(new OtpStoreData($user->id));
        $this->assertEquals(1, Otp::where('user_id', $user->id)->count());
        $firstOtp = Otp::where('user_id', $user->id)->first();
        $this->assertTrue($firstOtp->expires_at->isFuture());

        // Segundo OTP (debería expirar el primero)
        app(OtpStoreLogic::class)->run(new OtpStoreData($user->id));
        $this->assertEquals(2, Otp::where('user_id', $user->id)->count());

        $firstOtp->refresh();
        $this->assertFalse($firstOtp->expires_at->isFuture());

        $activeOtps = Otp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->count();

        $this->assertEquals(1, $activeOtps);
    }

    public function test_cannot_create_otp_too_fast()
    {
        $user = User::factory()->create();
        Setting::where('name', 'otp_retry_after_seconds')->update(['value' => '60']);

        $logic = app(OtpStoreLogic::class);
        $logic->run(new OtpStoreData($user->id));

        $response = $logic->run(new OtpStoreData($user->id));

        $this->assertEquals(422, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Please wait', $content['message']);
    }

    public function test_cannot_create_too_many_otps()
    {
        $user = User::factory()->create();
        Setting::where('name', 'otp_max_attempts')->update(['value' => '2']);
        Setting::where('name', 'otp_retry_after_seconds')->update(['value' => '60']);

        // Simulamos que el tiempo de espera no aplica (retrocediendo el tiempo de creación)
        Otp::query()->insert([
            [
                'user_id' => $user->id,
                'token' => 'token1',
                'otp_code' => '1111',
                'expires_at' => now()->addMinutes(15),
                'created_at' => now()->subSeconds(70),
                'updated_at' => now()->subSeconds(70),
            ],
            [
                'user_id' => $user->id,
                'token' => 'token2',
                'otp_code' => '2222',
                'expires_at' => now()->addMinutes(15),
                'created_at' => now()->subSeconds(65),
                'updated_at' => now()->subSeconds(65),
            ],
        ]);

        $logic = app(OtpStoreLogic::class);
        $response = $logic->run(new OtpStoreData($user->id));

        $this->assertEquals(422, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Too many OTP requests', $content['message']);
    }
}
