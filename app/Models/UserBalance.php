<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UserBalance",
 *     type="object",
 *     title="User Balance",
 *     description="Баланс пользователя",
 *     @OA\Property(property="id", type="integer", example=1, description="ID записи"),
 *     @OA\Property(property="user_id", type="integer", example=1, description="ID пользователя"),
 *     @OA\Property(property="amount", type="number", format="float", example=350.00, description="Сумма баланса"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-26T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-26T10:00:00.000000Z")
 * )
 */
class UserBalance extends Model
{
    protected $table = 'user_balance';

    protected $fillable = [
        'user_id',
        'amount',
    ];
}
