<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class InsufficientFundsException extends Exception
{
    public function __construct(string $message = 'Insufficient funds', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
