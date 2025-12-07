<?php

declare (strict_types=1);

namespace App\Dto\Request;

final readonly class TransferDto implements RequestDtoInterface
{
    public function __construct(
        public int $from_user_id,
        public int $to_user_id,
        public float $amount,
        public ?string $comment = null,
    ) {
    }

    public function getToUserId(): int
    {
        return $this->to_user_id;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getFromUserId(): int
    {
        return $this->from_user_id;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
