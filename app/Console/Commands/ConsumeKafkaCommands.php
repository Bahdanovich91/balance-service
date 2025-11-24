<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\MessageHandlers\BalanceCommandHandler;
use App\Services\KafkaService;
use Illuminate\Console\Command;

class ConsumeKafkaCommands extends Command
{
    protected $signature = 'kafka:consume';
    protected $description = 'Consume Kafka commands for balance service';

    public function handle(KafkaService $kafkaService, BalanceCommandHandler $handler): int
    {
        $this->info('Starting Kafka consumer for balance-commands...');
        $this->info('Press Ctrl+C to stop');

        $topic = config('kafka.topics.balance_commands');

        while (true) {
            try {
                $kafkaService->consume($topic, function (array $data) use ($handler) {
                    $this->info('Received command: ' . ($data['command'] ?? 'unknown'));
                    $handler->handle($data);
                });
            } catch (\Throwable $e) {
                $this->error('Error consuming message: ' . $e->getMessage());
                sleep(5);
            }
        }

        return Command::SUCCESS;
    }
}

