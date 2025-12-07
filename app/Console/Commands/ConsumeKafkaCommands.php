<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Handler\KafkaMessage\MessageCommandHandler;
use App\Services\KafkaService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ConsumeKafkaCommands extends Command
{
    protected $signature = 'kafka:consume';

    protected $description = 'Consume Kafka commands for balance service';

    public function handle(KafkaService $kafkaService, MessageCommandHandler $handler): int
    {
        $this->info('Starting Kafka consumer for balance-commands...');
        $this->info('Press Ctrl+C to stop');

        $topic = config('kafka.topics.balance_commands');

        $startTime = time();
        while ((time() - $startTime) < 59) {
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

        return CommandAlias::SUCCESS;
    }
}
