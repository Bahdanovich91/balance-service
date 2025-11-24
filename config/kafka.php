<?php

declare(strict_types=1);

return [
    'broker' => env('KAFKA_BROKER', 'kafka:9092'),
    'topics' => [
        'balance_events' => env('KAFKA_TOPIC_BALANCE_EVENTS', 'balance-events'),
        'balance_commands' => env('KAFKA_TOPIC_BALANCE_COMMANDS', 'balance-commands'),
    ],
    'consumer_group' => env('KAFKA_CONSUMER_GROUP', 'balance-service'),
];

