<?php

declare(strict_types=1);

namespace App\Handler\KafkaMessage\Strategy;

use App\Services\UserBalanceService;
use Illuminate\Support\Facades\Log;

final class CheckCommandHandler implements CommandHandlerInterface
{
    public function __construct(private UserBalanceService $balanceService)
    {
    }

    public function isApplicable(string $command): bool
    {
        return $command === 'check_balance';
    }

    public function handle(array $data): void
    {
        $userId = (int)($data['user_id'] ?? 0);
        $requiredAmount = (float)($data['amount'] ?? 0);

        try {
            $balance = $this->balanceService->getBalance($userId);
            $sufficient = $balance >= $requiredAmount;

            Log::info('Balance check completed', [
                'user_id' => $userId,
                'balance' => $balance,
                'required' => $requiredAmount,
                'sufficient' => $sufficient,
            ]);
        } catch (\Throwable $e) {
            Log::error('Balance check failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }
    }
}
