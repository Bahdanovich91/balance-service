<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BalanceController extends Controller
{
    public function __construct(
        private readonly UserBalanceService $balanceService
    ) {
    }

    public function balance(int $userId): JsonResponse
    {
        try {
            $balance = $this->balanceService->getBalance($userId);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'success' => true,
            'user_id' => $userId,
            'balance' => $balance,
        ], Response::HTTP_OK);
    }
}
