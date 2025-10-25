<?php

declare(strict_types=1);

namespace App\ValueObject;

use App\Enums\TransactionType;

final class TransactionParams
{
    protected ?int $fromUserId = null;

    protected ?int $toUserId = null;

    protected ?TransactionType $type = null;

    protected ?float $amount = null;

    protected ?string $comment = null;

    public function getFromUserId(): ?int
    {
        return $this->fromUserId;
    }

    public function setFromUserId(?int $fromUserId): static
    {
        $this->fromUserId = $fromUserId;

        return $this;
    }

    public function getToUserId(): ?int
    {
        return $this->toUserId;
    }

    public function setToUserId(?int $toUserId): static
    {
        $this->toUserId = $toUserId;

        return $this;
    }

    public function getType(): ?TransactionType
    {
        return $this->type;
    }

    public function setType(TransactionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'from_user_id' => $this->fromUserId,
            'to_user_id' => $this->toUserId,
            'type' => $this->type?->value,
            'amount' => $this->amount,
            'comment' => $this->comment,
        ];
    }
}
