<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\DepositDto;
use App\Dto\TransferDto;
use App\Dto\WithdrawDto;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\UserNotFoundException;
use App\Repositories\UserBalanceRepository;
use Illuminate\Support\Facades\DB;

readonly class UserBalanceService
{
    public function __construct(
        private UserBalanceRepository $userBalanceRepository,
        private TransactionService    $transactionService,
    ) {
    }

    public function deposit(DepositDto $depositDto): array
    {
        return DB::transaction(function () use ($depositDto) {
            $userBalance = $this->userBalanceRepository->findOrCreate($depositDto->user_id);
            $newBalance = $userBalance->amount + $depositDto->amount;

            $this->userBalanceRepository->updateBalance($userBalance, $newBalance);

            $transaction = $this->transactionService->create(
                toUserId: $depositDto->user_id,
                amount: $depositDto->amount,
                type: TransactionType::Deposit,
                comment: $depositDto->comment
            );

            return [
                'transaction' => $transaction,
                'new_balance' => $newBalance,
            ];
        });
    }

    public function withdraw(WithdrawDto $withdrawDto): array
    {
        return DB::transaction(function () use ($withdrawDto) {
            $userBalance = $this->userBalanceRepository->findOrFail($withdrawDto->user_id);

            if ($userBalance->amount < $withdrawDto->amount) {
                throw new InsufficientFundsException();
            }

            $newBalance = $userBalance->amount - $withdrawDto->amount;
            $this->userBalanceRepository->updateBalance($userBalance, $newBalance);

            $transaction = $this->transactionService->create(
                toUserId: $withdrawDto->user_id,
                amount: $withdrawDto->amount,
                type: TransactionType::Withdraw,
                comment: $withdrawDto->comment
            );

            return [
                'transaction' => $transaction,
                'new_balance' => $newBalance,
            ];
        });
    }

    public function transfer(TransferDto $transferDto): array
    {
        return DB::transaction(function () use ($transferDto) {
            $fromUserBalance = $this->userBalanceRepository->findOrFail($transferDto->from_user_id);
            $toUserBalance = $this->userBalanceRepository->findOrCreate($transferDto->to_user_id);

            if ($fromUserBalance->amount < $transferDto->amount) {
                throw new InsufficientFundsException();
            }

            $newFromBalance = $fromUserBalance->amount - $transferDto->amount;
            $newToBalance = $toUserBalance->amount + $transferDto->amount;

            $this->userBalanceRepository->updateBalance($fromUserBalance, $newFromBalance);
            $this->userBalanceRepository->updateBalance($toUserBalance, $newToBalance);

            $outTransaction = $this->transactionService->create(
                toUserId: $transferDto->to_user_id,
                amount: $transferDto->amount,
                type: TransactionType::TransferOut,
                fromUserId: $transferDto->from_user_id,
                comment: $transferDto->comment
            );

            $inTransaction = $this->transactionService->create(
                toUserId: $transferDto->to_user_id,
                amount: $transferDto->amount,
                type: TransactionType::TransferIn,
                fromUserId: $transferDto->from_user_id,
                comment: $transferDto->comment
            );

            return [
                'out_transaction' => $outTransaction,
                'in_transaction' => $inTransaction,
                'from_user_balance' => $newFromBalance,
                'to_user_balance' => $newToBalance,
            ];
        });
    }

    public function getBalance(int $userId): float
    {
        $userBalance = $this->userBalanceRepository->findByUserId($userId);
        if (!$userBalance) {
            throw new UserNotFoundException($userId);
        }

        return (float)$userBalance->amount;
    }
}
