<?php

declare(strict_types=1);

namespace App\Logging;

use Elastic\Elasticsearch\ClientBuilder;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class ElasticsearchLogger extends AbstractProcessingHandler
{
    private $client;

    private string $index;

    public function __construct()
    {
        parent::__construct();

        $hosts = config('elasticsearch.hosts');
        $this->client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();

        $this->index = config('elasticsearch.index');
    }

    protected function write(LogRecord $record): void
    {
        try {
            $params = [
                'index' => $this->index,
                'body' => [
                    'timestamp' => $record->datetime->format('c'),
                    'level' => $record->level->getName(),
                    'message' => $record->message,
                    'context' => $record->context,
                    'service' => config('elasticsearch.service'),
                ],
            ];

            $this->client->index($params);
        } catch (\Throwable $e) {
            error_log('Elasticsearch logging failed: ' . $e->getMessage());
        }
    }
}
