<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class UserNotFoundException extends Exception
{
    public function __construct(int $userId, string $message = 'User not found', int $code = 404)
    {
        parent::__construct("$message (ID: $userId)", $code);
    }
}
