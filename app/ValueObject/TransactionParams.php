<?php

declare(strict_types=1);

namespace App\ValueObject;

use App\Enums\TransactionType;

final class TransactionParams
{
    public function __construct(
        private readonly int $toUserId,
        private readonly float $amount,
        private readonly TransactionType $type,
        private readonly ?int $fromUserId = null,
        private readonly ?string $comment = null,
    ) {
    }

    public function getFromUserId(): ?int
    {
        return $this->fromUserId;
    }

    public function getToUserId(): int
    {
        return $this->toUserId;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function toArray(): array
    {
        return [
            'from_user_id' => $this->fromUserId,
            'to_user_id' => $this->toUserId,
            'type' => $this->type->value,
            'amount' => $this->amount,
            'comment' => $this->comment,
        ];
    }
}
