<?php

declare(strict_types=1);

namespace App\Handler\Logging;

use App\Services\ElasticsearchClientService;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

final class ElasticsearchLogger extends AbstractProcessingHandler
{
    public function __construct(private ElasticsearchClientService $clientService)
    {
        parent::__construct();
    }

    protected function write(LogRecord $record): void
    {
        $this->clientService->log([
            'timestamp' => $record->datetime->format('c'),
            'level' => $record->level->getName(),
            'message' => $record->message,
            'context' => $record->context,
        ]);
    }
}
