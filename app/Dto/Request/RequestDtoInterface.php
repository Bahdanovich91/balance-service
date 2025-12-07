<?php

namespace App\Dto\Request;

interface RequestDtoInterface
{
    public function getToUserId(): int;

    public function getAmount(): float;

    public function getFromUserId(): ?int;

    public function getComment(): ?string;
}
