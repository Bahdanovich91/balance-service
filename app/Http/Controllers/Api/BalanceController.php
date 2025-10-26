<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LogService;
use App\Services\UserBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Balance Service API",
 *     version="1.0.0",
 *     description="API для работы с балансом пользователей"
 * )
 * @OA\Server(
 *     url="http://localhost:8080",
 *     description="Development Server"
 * )
 */
class BalanceController extends Controller
{
    public function __construct(
        private readonly UserBalanceService $balanceService,
        private readonly LogService $log
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/balance/{userId}",
     *     summary="Получить баланс пользователя",
     *     description="Возвращает текущий баланс пользователя по его ID",
     *     operationId="getBalance",
     *     tags={"Balance"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         description="ID пользователя",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="balance", type="number", format="float", example=350.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пользователь не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found (ID: 1)")
     *         )
     *     )
     * )
     */
    public function balance(int $userId): JsonResponse
    {
        try {
            $balance = $this->balanceService->getBalance($userId);
        } catch (\Throwable $e) {
            $this->log->write('balance_errors.log', sprintf(
                'Balance request failed - User ID: %d, Error: %s',
                $userId,
                $e->getMessage()
            ));

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
