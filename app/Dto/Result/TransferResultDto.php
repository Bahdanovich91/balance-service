<?php

declare(strict_types=1);

namespace App\Dto\Result;

use App\Models\Transaction;

final readonly class TransferResultDto
{
    public function __construct(
        public Transaction $outTransaction,
        public Transaction $inTransaction,
        public float $fromUserBalance,
        public float $toUserBalance,
    ) {
    }
}
