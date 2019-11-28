<?php

namespace HowDoo\Auth;

use App\Repositories\ApiTokenRepository;
use Illuminate\Auth\TokenGuard;

/**
 * Class ServiceProvider
 *
 * @author Mirlan Jelamanov <mirlan.jelamanov@gmail.com>
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->app['auth']->provider('api-token', function () {
            return new UserProvider($this->app['auth']->createUserProvider(), $this->app->make(ApiTokenRepository::class));
        });

        $this->app['auth']->extend('token', function ($app, string $name, array $config) {
            return new TokenGuard(
                $this->app['auth']->createUserProvider($config['provider'] ?? null),
                $this->app['request'],
                $config['input_key'] ?? 'api_token',
                $config['storage_key'] ?? 'api_token',
                $config['hash'] ?? false
            );
        });
    }

    /**
     * @inheritDoc
     */
    public function register()
    {

    }
}
