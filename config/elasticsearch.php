<?php

return [
    'hosts' => [
        env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'),
    ],

    'index' => env('ELASTICSEARCH_INDEX', 'microservices-logs'),

    'service' => env('APP_SERVICE_NAME', 'balance-service'),
];
