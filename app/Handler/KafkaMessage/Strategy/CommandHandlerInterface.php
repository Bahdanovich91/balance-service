<?php

declare(strict_types=1);

namespace App\Handler\KafkaMessage\Strategy;

interface CommandHandlerInterface
{
    public function isApplicable(string $command): bool;

    public function handle(array $data): void;
}
