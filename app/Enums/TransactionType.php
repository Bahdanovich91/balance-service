<?php

namespace App\Enums;

enum TransactionType: string
{
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    case Deposit = 'deposit';
    case Withdraw = 'withdraw';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
}
