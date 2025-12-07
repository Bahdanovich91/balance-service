<?php

declare(strict_types=1);

namespace App\Handler\Balance;

use App\Dto\Request\WithdrawDto;
use App\Dto\Result\WithdrawResultDto;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Repositories\UserBalanceRepository;
use App\Services\TransactionService;
use Illuminate\Support\Facades\DB;

readonly class WithdrawHandler
{
    public function __construct(
        private UserBalanceRepository $userBalanceRepository,
        private TransactionService    $transactionService,
    ) {
    }

    public function __invoke(WithdrawDto $dto): WithdrawResultDto
    {
        DB::beginTransaction();
        try {
            $userBalance = $this->userBalanceRepository->findOrFail($dto->user_id);
            if ($userBalance->amount < $dto->amount) {
                throw new InsufficientFundsException();
            }

            $newUserBalance = $userBalance->amount - $dto->amount;
            $this->userBalanceRepository->updateBalance($userBalance, $newUserBalance);

            $transaction = $this->transactionService->createFromDto($dto, TransactionType::Withdraw);

            DB::commit();

            return new WithdrawResultDto($transaction, $newUserBalance);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
