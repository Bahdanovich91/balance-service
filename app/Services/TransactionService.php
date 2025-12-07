<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\Request\RequestDtoInterface;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\ValueObject\TransactionParams;

readonly class TransactionService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
    ) {
    }

    public function createFromDto(RequestDtoInterface $dto, TransactionType $type): Transaction
    {
        $params = new TransactionParams(
            toUserId:   $dto->getToUserId(),
            amount:     $dto->getAmount(),
            type:       $type,
            fromUserId: $dto->getFromUserId(),
            comment:    $dto->getComment(),
        );

        return $this->transactionRepository->create($params);
    }
}
