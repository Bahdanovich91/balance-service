<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Services\UserBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TransactionController extends Controller
{
    public function __construct(
        private readonly UserBalanceService $balanceService
    ) {
    }

    public function deposit(DepositRequest $request): JsonResponse
    {
        try {
            $result = $this->balanceService->deposit($request->validated());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(
            [
            'success' => true,
            'message' => 'Средства успешно зачислены',
            'transaction' => $result['transaction'],
            'new_balance' => $result['new_balance'],
        ],
            Response::HTTP_OK
        );
    }

    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        try {
            $result = $this->balanceService->withdraw($request->validated());
        } catch (\Throwable $e) {
            return response()->json(
                [
                'success' => false,
                'message' => $e->getMessage(),
            ],
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Средства успешно списаны',
            'transaction' => $result['transaction'],
            'new_balance' => $result['new_balance'],
        ], Response::HTTP_OK);
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        try {
            $result = $this->balanceService->transfer($request->validated());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'success' => true,
            'message' => 'Перевод выполнен успешно',
            'out_transaction' => $result['out_transaction'],
            'in_transaction' => $result['in_transaction'],
            'from_user_balance' => $result['from_user_balance'],
            'to_user_balance' => $result['to_user_balance'],
        ], Response::HTTP_OK);
    }
}
