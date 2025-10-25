<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\UserNotFoundException;
use App\Models\UserBalance;

class UserBalanceRepository
{
    public function findByUserId(int $userId): ?UserBalance
    {
        return UserBalance::where('user_id', $userId)->first();
    }

    public function findOrFail(int $userId): UserBalance
    {
        $userBalance = $this->findByUserId($userId);
        if (!$userBalance) {
            throw new UserNotFoundException($userId);
        }

        return $userBalance;
    }

    public function create(int $userId, float $amount = 0.0): UserBalance
    {
        return UserBalance::create([
            'user_id' => $userId,
            'amount' => $amount,
        ]);
    }

    public function updateBalance(UserBalance $userBalance, float $newBalance): UserBalance
    {
        $userBalance->update(['amount' => $newBalance]);

        return $userBalance->fresh();
    }

    public function findOrCreate(int $userId): UserBalance
    {
        $userBalance = $this->findByUserId($userId);
        if (!$userBalance) {
            $userBalance = $this->create($userId);
        }

        return $userBalance;
    }
}
