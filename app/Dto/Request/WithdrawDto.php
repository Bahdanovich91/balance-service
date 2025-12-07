<?php

declare (strict_types=1);

namespace App\Dto\Request;

final readonly class WithdrawDto implements RequestDtoInterface
{
    public function __construct(
        public int $user_id,
        public float $amount,
        public ?string $comment = null,
    ) {
    }

    public function getToUserId(): int
    {
        return $this->user_id;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getFromUserId(): null
    {
        return null;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
