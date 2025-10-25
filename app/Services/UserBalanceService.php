<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Repositories\UserBalanceRepository;
use Illuminate\Support\Facades\DB;

class UserBalanceService
{
    public function __construct(
        private readonly UserBalanceRepository $userBalanceRepository,
        private readonly TransactionService    $transactionService,
    ) {
    }

    public function deposit(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $userBalance = $this->userBalanceRepository->findOrCreate($data['user_id']);
            $newBalance = $userBalance->amount + $data['amount'];

            $this->userBalanceRepository->updateBalance($userBalance, $newBalance);

            $transaction = $this->transactionService->create(
                toUserId: $data['user_id'],
                amount: $data['amount'],
                type: TransactionType::Deposit,
                comment: $data['comment']
            );

            return [
                'transaction' => $transaction,
                'new_balance' => $newBalance,
            ];
        });
    }

    public function withdraw(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $userBalance = $this->userBalanceRepository->findOrFail($data['user_id']);

            if ($userBalance->amount < $data['amount']) {
                throw new InsufficientFundsException();
            }

            $newBalance = $userBalance->amount - $data['amount'];
            $this->userBalanceRepository->updateBalance($userBalance, $newBalance);

            $transaction = $this->transactionService->create(
                toUserId: $data['user_id'],
                amount: $data['amount'],
                type: TransactionType::Withdraw,
                comment: $data['comment']
            );

            return [
                'transaction' => $transaction,
                'new_balance' => $newBalance,
            ];
        });
    }

    public function transfer(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $fromUserBalance = $this->userBalanceRepository->findOrFail($data['from_user_id']);
            $toUserBalance = $this->userBalanceRepository->findOrCreate($data['to_user_id']);

            if ($fromUserBalance->amount < $data['amount']) {
                throw new InsufficientFundsException();
            }

            // Обновляем балансы
            $newFromBalance = $fromUserBalance->amount - $data['amount'];
            $newToBalance = $toUserBalance->amount + $data['amount'];

            $this->userBalanceRepository->updateBalance($fromUserBalance, $newFromBalance);
            $this->userBalanceRepository->updateBalance($toUserBalance, $newToBalance);

            $outTransaction = $this->transactionService->create(
                toUserId: $data['to_user_id'],
                amount: $data['amount'],
                type: TransactionType::TransferOut,
                fromUserId: $data['from_user_id'],
                comment: $data['comment']
            );

            $inTransaction = $this->transactionService->create(
                toUserId: $data['to_user_id'],
                amount: $data['amount'],
                type: TransactionType::TransferIn,
                fromUserId: $data['from_user_id'],
                comment: $data['comment']
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
        $userBalance = $this->userBalanceRepository->findOrCreate($userId);

        return (float)$userBalance->amount;
    }
}
