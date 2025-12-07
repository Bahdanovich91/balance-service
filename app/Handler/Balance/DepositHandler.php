<?php

declare(strict_types=1);

namespace App\Handler\Balance;

use App\Dto\Request\DepositDto;
use App\Dto\Result\DepositResultDto;
use App\Enums\TransactionType;
use App\Repositories\UserBalanceRepository;
use App\Services\TransactionService;
use Illuminate\Support\Facades\DB;

readonly class DepositHandler
{
    public function __construct(
        private UserBalanceRepository $userBalanceRepository,
        private TransactionService    $transactionService,
    ) {
    }

    public function __invoke(DepositDto $dto): DepositResultDto
    {
        DB::beginTransaction();

        try {
            $userBalance = $this->userBalanceRepository->findOrCreate($dto->user_id);
            $newUserBalance = $userBalance->amount + $dto->amount;

            $this->userBalanceRepository->updateBalance($userBalance, $newUserBalance);

            $transaction = $this->transactionService->createFromDto($dto, TransactionType::Deposit);

            DB::commit();

            return new DepositResultDto($transaction, $newUserBalance);
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
