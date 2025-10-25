<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\ValueObject\TransactionParams;

class TransactionService
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
    ) {
    }

    public function create(
        int             $toUserId,
        float           $amount,
        TransactionType $type,
        ?int            $fromUserId = null,
        ?string         $comment = null,
    ): Transaction {
        return $this->transactionRepository->create(
            (new TransactionParams())
                ->setFromUserId($fromUserId)
                ->setToUserId($toUserId)
                ->setAmount($amount)
                ->setType($type)
                ->setComment($comment)
        );
    }
}
