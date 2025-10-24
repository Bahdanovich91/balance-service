<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
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
