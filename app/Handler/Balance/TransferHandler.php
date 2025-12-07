<?php

declare(strict_types=1);

namespace App\Handler\Balance;

use App\Dto\Request\TransferDto;
use App\Dto\Result\TransferResultDto;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Repositories\UserBalanceRepository;
use App\Services\TransactionService;
use Illuminate\Support\Facades\DB;

readonly class TransferHandler
{
    public function __construct(
        private UserBalanceRepository $userBalanceRepository,
        private TransactionService    $transactionService,
    ) {
    }

    public function __invoke(TransferDto $dto): TransferResultDto
    {
        DB::beginTransaction();

        try {
            $fromUserBalance = $this->userBalanceRepository->findOrFail($dto->from_user_id);
            $toUserBalance = $this->userBalanceRepository->findOrCreate($dto->to_user_id);

            if ($fromUserBalance->amount < $dto->amount) {
                throw new InsufficientFundsException();
            }

            $newFromUserBalance = $fromUserBalance->amount - $dto->amount;
            $newToUserBalance = $toUserBalance->amount + $dto->amount;

            $this->userBalanceRepository->updateBalance($fromUserBalance, $newFromUserBalance);
            $this->userBalanceRepository->updateBalance($toUserBalance, $newToUserBalance);

            $outTransaction = $this->transactionService->createFromDto($dto, TransactionType::TransferOut);
            $inTransaction = $this->transactionService->createFromDto($dto, TransactionType::TransferIn);

            DB::commit();

            return new TransferResultDto(
                $outTransaction,
                $inTransaction,
                $newFromUserBalance,
                $newToUserBalance
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
