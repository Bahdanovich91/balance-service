<?php

declare(strict_types=1);

namespace App\Handler\KafkaMessage\Strategy;

use App\Dto\Request\WithdrawDto;
use App\Handler\Balance\WithdrawHandler;
use App\Services\KafkaService;
use Illuminate\Support\Facades\Log;

final readonly class WithdrawCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private WithdrawHandler    $withdrawHandler,
        private KafkaService       $kafkaService,
    ) {
    }

    public function isApplicable(string $command): bool
    {
        return $command === 'withdraw';
    }

    public function handle(array $data): void
    {
        $withdrawDto = new WithdrawDto(user_id: $data['user_id'], amount: $data['amount'], comment: 'Order payment via Kafka');

        try {
            $withdrawResultDto = ($this->withdrawHandler)($withdrawDto);

            $this->kafkaService->sendEvent([
                'type' => 'balance_withdrawn',
                'user_id' => $withdrawDto->user_id,
                'amount' => $withdrawDto->amount,
                'new_balance' => $withdrawResultDto->newBalance,
                'transaction_id' => $withdrawResultDto->transaction->id,
                'timestamp' => now()->toIso8601String(),
            ]);

            Log::info(
                'Balance withdrawal completed',
                [
                    'user_id' => $withdrawDto->user_id,
                    'amount' => $withdrawDto->amount,
                    'new_balance' => $withdrawResultDto->newBalance
                ]
            );
        } catch (\Throwable $e) {
            Log::error(
                'Balance withdrawal failed',
                [
                    'user_id' => $withdrawDto->user_id,
                    'amount' => $withdrawDto->amount,
                    'error' => $e->getMessage()
                ]
            );
        }
    }
}
