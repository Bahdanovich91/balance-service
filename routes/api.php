<?php

use App\Http\Controllers\Api\BalanceController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/deposit', [TransactionController::class, 'deposit']);
Route::post('/withdraw', [TransactionController::class, 'withdraw']);
Route::post('/transfer', [TransactionController::class, 'transfer']);
Route::get('/balance/{userId}', [BalanceController::class, 'balance']);
