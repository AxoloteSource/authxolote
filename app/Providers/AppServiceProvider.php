<?php

namespace App\Providers;

use App\Contracts\OtpSender;
use App\Delivery\FakeOtpSender;
use App\Delivery\MessagesOtpSender;
use AxoloteSource\Logics\Responses\ResponseMacros;
use Illuminate\Support\ServiceProvider;
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

        ResponseMacros::register();
    }
}
