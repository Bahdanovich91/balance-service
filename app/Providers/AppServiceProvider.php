<?php

declare(strict_types=1);

namespace App\Providers;

use App\Handler\KafkaMessage\MessageCommandHandler;
use App\Handler\KafkaMessage\Strategy\CheckCommandHandler;
use App\Handler\KafkaMessage\Strategy\WithdrawCommandHandler;
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

        $this->app->tag([
            CheckCommandHandler::class,
            WithdrawCommandHandler::class,
        ], 'balance.handlers');

        $this->app->bind(MessageCommandHandler::class, fn($app) => new MessageCommandHandler($app->tagged('balance.handlers')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
