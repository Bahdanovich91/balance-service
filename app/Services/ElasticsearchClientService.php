<?php

declare(strict_types=1);

namespace App\Services;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

final class ElasticsearchClientService
{
    private Client $client;

    private string $index;

    private string $service;

    public function __construct(array $hosts, string $index, string $service)
    {
        $this->client = ClientBuilder::create()->setHosts($hosts)->build();
        $this->index = $index;
        $this->service = $service;
    }

    public function log(array $data): void
    {
        try {
            $this->client->index([
                'index' => $this->index,
                'body' => array_merge($data, ['service' => $this->service]),
            ]);
        } catch (\Throwable $e) {
            error_log('Elasticsearch logging failed: ' . $e->getMessage());
        }
    }
}
