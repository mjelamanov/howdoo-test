<?php

namespace App\Providers;

use App\Repositories\ApiTokenRepository;
use App\Repositories\ApiTokenRepositoryEloquent;
use App\Repositories\DocumentRepository;
use App\Repositories\DocumentRepositoryEloquent;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(DocumentRepository::class, DocumentRepositoryEloquent::class);
        $this->app->bind(UserRepository::class, UserRepositoryEloquent::class);
        $this->app->bind(ApiTokenRepository::class, ApiTokenRepositoryEloquent::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
