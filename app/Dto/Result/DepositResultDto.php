<?php

declare(strict_types=1);

namespace App\Dto\Result;

use App\Models\Transaction;

final readonly class DepositResultDto
{
    public function __construct(
        public Transaction $transaction,
        public float $newBalance,
    ) {
    }
}
