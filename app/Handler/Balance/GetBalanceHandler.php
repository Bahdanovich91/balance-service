<?php

declare(strict_types=1);

namespace App\Handler\Balance;

use App\Exceptions\UserNotFoundException;
use App\Repositories\UserBalanceRepository;

readonly class GetBalanceHandler
{
    public function __construct(
        private UserBalanceRepository $userBalanceRepository
    ) {
    }

    public function __invoke(int $userId): float
    {
        $userBalance = $this->userBalanceRepository->findByUserId($userId);
        if (!$userBalance) {
            throw new UserNotFoundException($userId);
        }

        return (float) $userBalance->amount;
    }
}
