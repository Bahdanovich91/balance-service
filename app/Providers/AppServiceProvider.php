<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\KafkaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(KafkaService::class, fn($app) => new KafkaService(
            config('kafka.broker'),
            config('kafka.topics.balance_events')
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
