<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\ConsumeKafkaCommands;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        ConsumeKafkaCommands::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('kafka:consume')
            ->everyMinute()
            ->appendOutputTo(storage_path('logs/kafka.log'))
            ->runInBackground();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        if (file_exists(base_path('routes/console.php'))) {
            require base_path('routes/console.php');
        }
    }
}
