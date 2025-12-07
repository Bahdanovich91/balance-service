<?php

declare(strict_types=1);

namespace App\ValueObject;

use App\Enums\TransactionType;

final readonly class TransactionParams
{
    public function __construct(
        private int             $toUserId,
        private float           $amount,
        private TransactionType $type,
        private ?int            $fromUserId = null,
        private ?string         $comment = null,
    ) {
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
