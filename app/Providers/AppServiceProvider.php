<?php

namespace App\Providers;

use App\Domains\Authentication\Observers\UserObserver;
use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate as FrameworkAuthenticate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FrameworkAuthenticate::class, \App\Http\Middleware\Authenticate::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
    }
}
