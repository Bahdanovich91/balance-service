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

        $hosts = [env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200')];
        $this->client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();

        $this->index = env('ELASTICSEARCH_INDEX', 'microservices-logs');
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
                    'service' => 'balance-service',
                ],
            ];

            $this->client->index($params);
        } catch (\Throwable $e) {
            error_log('Elasticsearch logging failed: ' . $e->getMessage());
        }
    }
}

