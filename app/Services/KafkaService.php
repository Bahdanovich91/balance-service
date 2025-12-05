<?php

declare(strict_types=1);

namespace App\Services;

use Enqueue\RdKafka\RdKafkaConnectionFactory;
use Illuminate\Support\Facades\Log;

class KafkaService
{
    private $context;

    private $producer;

    private string $eventsTopic;

    public function __construct(string $kafkaBroker, string $eventsTopic = 'balance-events')
    {
        $this->eventsTopic = $eventsTopic;

        $factory = new RdKafkaConnectionFactory([
            'global' => [
                'group.id' => 'balance-service',
                'metadata.broker.list' => $kafkaBroker,
                'enable.auto.commit' => 'true',
            ],
        ]);

        $this->context = $factory->createContext();
        $this->producer = $this->context->createProducer();
    }

    public function sendEvent(array $data): void
    {
        try {
            $topic = $this->context->createTopic($this->eventsTopic);
            $message = $this->context->createMessage(
                json_encode($data, JSON_THROW_ON_ERROR)
            );

            $this->producer->send($topic, $message);

            Log::info('Kafka event sent', [
                'topic' => $this->eventsTopic,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send Kafka event', [
                'topic' => $this->eventsTopic,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function consume(string $topic, callable $handler, int $timeout = 5000): void
    {
        try {
            $kafkaTopic = $this->context->createTopic($topic);
            $consumer = $this->context->createConsumer($kafkaTopic);

            $message = $consumer->receive($timeout);
            if ($message) {
                $data = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);
                $handler($data, $message);
                $consumer->acknowledge($message);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to consume Kafka message', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
