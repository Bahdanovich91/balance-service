<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\ValueObject\TransactionParams;

class TransactionRepository
{
    public function create(
        TransactionParams $transactionParams,
    ): Transaction {
        $transaction = new Transaction($transactionParams->toArray());
        $transaction->save();

        return $transaction;
    }
}
