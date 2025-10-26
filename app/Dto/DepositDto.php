<?php

declare (strict_types=1);

namespace App\Dto;

final readonly class DepositDto
{
    public function __construct(
        public int $user_id,
        public float $amount,
        public ?string $comment = null,
    ) {
    }
}
