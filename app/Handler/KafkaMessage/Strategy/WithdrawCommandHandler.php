<?php

declare(strict_types=1);

namespace App\Handler\KafkaMessage\Strategy;

use App\Dto\WithdrawDto;
use App\Services\UserBalanceService;
use Illuminate\Support\Facades\Log;

final class WithdrawCommandHandler implements CommandHandlerInterface
{
    public function __construct(private UserBalanceService $balanceService)
    {
    }

    public function isApplicable(string $command): bool
    {
        return $command === 'withdraw';
    }

    public function handle(array $data): void
    {
        $userId = (int)($data['user_id'] ?? 0);
        $amount = (float)($data['amount'] ?? 0);

        try {
            $withdrawDto = new WithdrawDto(user_id: $userId, amount: $amount, comment: 'Order payment via Kafka');
            $this->balanceService->withdraw($withdrawDto);

            Log::info('Balance withdrawal completed', ['user_id' => $userId, 'amount' => $amount]);
        } catch (\Throwable $e) {
            Log::error('Balance withdrawal failed', ['user_id' => $userId, 'amount' => $amount, 'error' => $e->getMessage()]);
        }
    }
}
