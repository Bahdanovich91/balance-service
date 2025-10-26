<?php

declare (strict_types=1);

namespace App\Dto;

final readonly class TransferDto
{
    public function __construct(
        public int $from_user_id,
        public int $to_user_id,
        public float $amount,
        public ?string $comment = null,
    ) {
    }
}
