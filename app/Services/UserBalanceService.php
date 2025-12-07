<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\Request\DepositDto;
use App\Dto\Request\TransferDto;
use App\Dto\Request\WithdrawDto;
use App\Dto\Result\DepositResultDto;
use App\Dto\Result\TransferResultDto;
use App\Dto\Result\WithdrawResultDto;
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

    public function deposit(DepositDto $depositDto): DepositResultDto
    {
        return DB::transaction(function () use ($depositDto) {
            $userBalance = $this->userBalanceRepository->findOrCreate($depositDto->user_id);
            $newUserBalance = $userBalance->amount + $depositDto->amount;

            $this->userBalanceRepository->updateBalance($userBalance, $newUserBalance);

            $transaction = $this->transactionService->createFromDto($depositDto, TransactionType::Deposit);

            return new DepositResultDto($transaction, $newUserBalance);
        });
    }

    public function withdraw(WithdrawDto $withdrawDto): WithdrawResultDto
    {
        return DB::transaction(function () use ($withdrawDto) {
            $userBalance = $this->userBalanceRepository->findOrFail($withdrawDto->user_id);

            if ($userBalance->amount < $withdrawDto->amount) {
                throw new InsufficientFundsException();
            }

            $newUserBalance = $userBalance->amount - $withdrawDto->amount;
            $this->userBalanceRepository->updateBalance($userBalance, $newUserBalance);

            $transaction = $this->transactionService->createFromDto($withdrawDto, TransactionType::Withdraw);

            return new WithdrawResultDto($transaction, $newUserBalance);
        });
    }

    public function transfer(TransferDto $transferDto): TransferResultDto
    {
        return DB::transaction(function () use ($transferDto) {
            $fromUserBalance = $this->userBalanceRepository->findOrFail($transferDto->from_user_id);
            $toUserBalance = $this->userBalanceRepository->findOrCreate($transferDto->to_user_id);

            if ($fromUserBalance->amount < $transferDto->amount) {
                throw new InsufficientFundsException();
            }

            $newFromUserBalance = $fromUserBalance->amount - $transferDto->amount;
            $newToUserBalance = $toUserBalance->amount + $transferDto->amount;

            $this->userBalanceRepository->updateBalance($fromUserBalance, $newFromUserBalance);
            $this->userBalanceRepository->updateBalance($toUserBalance, $newToUserBalance);

            $outTransaction = $this->transactionService->createFromDto($transferDto, TransactionType::TransferOut);
            $inTransaction = $this->transactionService->createFromDto($transferDto, TransactionType::TransferIn);

            return new TransferResultDto(
                $outTransaction,
                $inTransaction,
                $newFromUserBalance,
                $newToUserBalance
            );
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
