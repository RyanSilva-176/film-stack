<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;

class SocialiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureSocialiteHttpClient();
    }

    private function configureSocialiteHttpClient(): void
    {
        $this->app->when(\Laravel\Socialite\Two\GoogleProvider::class)
            ->needs(\GuzzleHttp\ClientInterface::class)
            ->give(function () {
                return new Client([
                    'timeout' => 30.0,
                    'connect_timeout' => 15.0,
                    'verify' => true,
                    'http_errors' => true,
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                        CURLOPT_DNS_CACHE_TIMEOUT => 60,
                        CURLOPT_TCP_KEEPALIVE => 1,
                        CURLOPT_TCP_KEEPIDLE => 10,
                        CURLOPT_TCP_KEEPINTVL => 5,
                    ]
                ]);
            });
    }
}
