<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *     title="Transaction",
 *     description="Транзакция",
 *     @OA\Property(property="id", type="integer", example=1, description="ID транзакции"),
 *     @OA\Property(property="from_user_id", type="integer", nullable=true, example=1, description="ID пользователя-отправителя"),
 *     @OA\Property(property="to_user_id", type="integer", nullable=true, example=2, description="ID пользователя-получателя"),
 *     @OA\Property(property="type", type="string", enum={"deposit", "withdraw", "transfer_in", "transfer_out"}, example="deposit", description="Тип транзакции"),
 *     @OA\Property(property="amount", type="number", format="float", example=500.00, description="Сумма транзакции"),
 *     @OA\Property(property="comment", type="string", nullable=true, example="Пополнение через карту", description="Комментарий к транзакции"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-26T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-26T10:00:00.000000Z")
 * )
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'type',
        'amount',
        'comment',
    ];

    protected $casts = [
        'type' => TransactionType::class,
    ];
}
