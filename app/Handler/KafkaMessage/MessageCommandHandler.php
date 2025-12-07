<?php

declare(strict_types=1);

namespace App\Handler\KafkaMessage;

use App\Handler\KafkaMessage\Strategy\CommandHandlerInterface;
use Illuminate\Support\Facades\Log;

final class MessageCommandHandler
{
    /** @var iterable<CommandHandlerInterface> */
    private iterable $handlers;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function handle(array $data): void
    {
        $command = $data['command'] ?? null;

        foreach ($this->handlers as $handler) {
            if ($handler->isApplicable($command)) {
                $handler->handle($data);

                return;
            }
        }

        Log::warning('Unknown balance command', ['command' => $command]);
    }
}
