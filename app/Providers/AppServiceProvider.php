<?php

namespace App\Providers;

use App\Contracts\OtpSender;
use App\Delivery\FakeOtpSender;
use App\Delivery\MessagesOtpSender;
use AxoloteSource\Logics\Responses\ResponseMacros;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OtpSender::class, function () {
            return match (config('services.messages.driver')) {
                'fake' => new FakeOtpSender,
                default => $this->app->make(MessagesOtpSender::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::enablePasswordGrant();

        RateLimiter::for('login', function (Request $request): array {
            $email = $request->input('email');
            $account = is_string($email) ? Str::lower(trim($email)) : '';

            return [
                Limit::perMinutes(15, 5)->by('login:account:'.sha1($account)),
                Limit::perMinute(30)->by('login:ip:'.$request->ip()),
            ];
        });

        ResponseMacros::register();
    }
}
