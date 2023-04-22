<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransactionController;

// Маршруты для работы с кошельками
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/wallets', [WalletController::class, 'index']);
    Route::post('/wallets', [WalletController::class, 'store']);
    Route::get('/wallets/{wallet}', [WalletController::class, 'show']);
    Route::put('/wallets/{wallet}', [WalletController::class, 'update']);
    Route::delete('/wallets/{wallet}', [WalletController::class, 'destroy']);
});

// Мар шруты для работы с транзакциями
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);
    Route::post('/transactions/create-request', [TransactionController::class, 'createRequest']);
    Route::get('/transactions/filter', [TransactionController::class, 'filter']);
    Route::post('/transactions/execute-request', [TransactionController::class, 'executeRequest']);
});


// Маршрут для получения суммы комиссий за определенный период
Route::middleware('auth:sanctum')->get('/system-fees', [TransactionController::class, 'getSystemFees']);

