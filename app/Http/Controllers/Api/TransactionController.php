<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Services\LogService;
use App\Services\UserBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;

class TransactionController extends Controller
{
    public function __construct(
        private readonly UserBalanceService $balanceService,
        private readonly LogService $log
    ) {
    }

    /**
     * @OA\Post(
     *     path="/api/deposit",
     *     summary="Зачисление средств",
     *     description="Зачисляет средства на баланс пользователя",
     *     operationId="deposit",
     *     tags={"Transactions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "amount"},
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID пользователя"),
     *             @OA\Property(property="amount", type="number", format="float", example=500.00, description="Сумма для зачисления"),
     *             @OA\Property(property="comment", type="string", example="Пополнение через карту", description="Комментарий к операции")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Средства успешно зачислены",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Средства успешно зачислены"),
     *             @OA\Property(property="transaction", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="to_user_id", type="integer", example=1),
     *                 @OA\Property(property="type", type="string", example="deposit"),
     *                 @OA\Property(property="amount", type="number", format="float", example=500.00),
     *                 @OA\Property(property="comment", type="string", example="Пополнение через карту")
     *             ),
     *             @OA\Property(property="new_balance", type="number", format="float", example=500.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function deposit(DepositRequest $request): JsonResponse
    {
        try {
            $result = $this->balanceService->deposit($request->validated());
        } catch (\Throwable $e) {
            $data = $request->only(['user_id', 'amount']);
            $this->log->write('deposits_errors.log', sprintf(
                'Deposit failed - User ID: %d, Amount: %.2f, Error: %s',
                $data['user_id'],
                $data['amount'],
                $e->getMessage()
            ));

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

    /**
     * @OA\Post(
     *     path="/api/withdraw",
     *     summary="Списание средств",
     *     description="Списывает средства с баланса пользователя",
     *     operationId="withdraw",
     *     tags={"Transactions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "amount"},
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID пользователя"),
     *             @OA\Property(property="amount", type="number", format="float", example=200.00, description="Сумма для списания"),
     *             @OA\Property(property="comment", type="string", example="Покупка подписки", description="Комментарий к операции")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Средства успешно списаны",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Средства успешно списаны"),
     *             @OA\Property(property="transaction", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="to_user_id", type="integer", example=1),
     *                 @OA\Property(property="type", type="string", example="withdraw"),
     *                 @OA\Property(property="amount", type="number", format="float", example=200.00),
     *                 @OA\Property(property="comment", type="string", example="Покупка подписки")
     *             ),
     *             @OA\Property(property="new_balance", type="number", format="float", example=300.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Недостаточно средств",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Insufficient funds")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        try {
            $result = $this->balanceService->withdraw($request->validated());
        } catch (\Throwable $e) {
            $data = $request->only(['user_id', 'amount']);
            $this->log->write('withdrawals_errors.log', sprintf(
                'Withdrawal failed - User ID: %d, Amount: %.2f, Error: %s',
                $data['user_id'],
                $data['amount'],
                $e->getMessage()
            ));

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

    /**
     * @OA\Post(
     *     path="/api/transfer",
     *     summary="Перевод между пользователями",
     *     description="Переводит средства от одного пользователя к другому",
     *     operationId="transfer",
     *     tags={"Transactions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"from_user_id", "to_user_id", "amount"},
     *             @OA\Property(property="from_user_id", type="integer", example=1, description="ID пользователя-отправителя"),
     *             @OA\Property(property="to_user_id", type="integer", example=2, description="ID пользователя-получателя"),
     *             @OA\Property(property="amount", type="number", format="float", example=150.00, description="Сумма для перевода"),
     *             @OA\Property(property="comment", type="string", example="Перевод другу", description="Комментарий к операции")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Перевод выполнен успешно",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Перевод выполнен успешно"),
     *             @OA\Property(property="out_transaction", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="from_user_id", type="integer", example=1),
     *                 @OA\Property(property="to_user_id", type="integer", example=2),
     *                 @OA\Property(property="type", type="string", example="transfer_out"),
     *                 @OA\Property(property="amount", type="number", format="float", example=150.00),
     *                 @OA\Property(property="comment", type="string", example="Перевод другу")
     *             ),
     *             @OA\Property(property="in_transaction", type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="from_user_id", type="integer", example=1),
     *                 @OA\Property(property="to_user_id", type="integer", example=2),
     *                 @OA\Property(property="type", type="string", example="transfer_in"),
     *                 @OA\Property(property="amount", type="number", format="float", example=150.00),
     *                 @OA\Property(property="comment", type="string", example="Перевод другу")
     *             ),
     *             @OA\Property(property="from_user_balance", type="number", format="float", example=350.00),
     *             @OA\Property(property="to_user_balance", type="number", format="float", example=650.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Недостаточно средств",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Insufficient funds")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function transfer(TransferRequest $request): JsonResponse
    {
        try {
            $result = $this->balanceService->transfer($request->validated());
        } catch (\Throwable $e) {
            $data = $request->only(['from_user_id', 'to_user_id', 'amount']);
            $this->log->write('transfers_errors.log', sprintf(
                'Transfer failed - From User ID: %d, To User ID: %d, Amount: %.2f, Error: %s',
                $data['from_user_id'],
                $data['to_user_id'],
                $data['amount'],
                $e->getMessage()
            ));

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
