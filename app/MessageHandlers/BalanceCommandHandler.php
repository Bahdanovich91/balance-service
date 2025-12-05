<?php

declare(strict_types=1);

namespace App\MessageHandlers;

use App\Services\UserBalanceService;
use Illuminate\Support\Facades\Log;

readonly class BalanceCommandHandler
{
    public function __construct(
        private UserBalanceService $balanceService
    ) {
    }

    public function handle(array $data): void
    {
        $command = $data['command'] ?? null;
        $orderId = $data['order_id'] ?? null;

        Log::info('Balance command received', [
            'command' => $command,
            'order_id' => $orderId,
            'data' => $data,
        ]);

        match ($command) {
            'check_balance' => $this->handleCheckBalance($data),
            'withdraw' => $this->handleWithdraw($data),
            default => Log::warning('Unknown balance command', ['command' => $command]),
        };
    }

    private function handleCheckBalance(array $data): void
    {
        $userId = (int) ($data['user_id'] ?? 0);
        $requiredAmount = (float) ($data['amount'] ?? 0);

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
            Log::error('Balance check failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function handleWithdraw(array $data): void
    {
        $userId = (int) ($data['user_id'] ?? 0);
        $amount = (float) ($data['amount'] ?? 0);

        try {
            $balance = $this->balanceService->getBalance($userId);

            if ($balance < $amount) {
                Log::warning('Insufficient balance for withdrawal', [
                    'user_id' => $userId,
                    'balance' => $balance,
                    'amount' => $amount,
                ]);

                return;
            }

            // Используем существующий метод withdraw через DTO
            $withdrawDto = new \App\Dto\WithdrawDto(
                user_id: $userId,
                amount: $amount,
                comment: 'Order payment via Kafka'
            );

            $this->balanceService->withdraw($withdrawDto);

            Log::info('Balance withdrawal completed via Kafka', [
                'user_id' => $userId,
                'amount' => $amount,
            ]);
        } catch (\Throwable $e) {
            Log::error('Balance withdrawal failed via Kafka', [
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
