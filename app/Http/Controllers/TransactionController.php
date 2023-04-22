<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Resources\TransactionResource;
use App\Models\Wallet;

use DB;





class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transactions = Transaction::all();
        return response()->json($transactions);
    }

    /**
     * Store a newly created transaction in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'from_wallet_id' => 'required|integer|exists:wallets,id',
            'to_wallet_id' => 'required|integer|exists:wallets,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'commission_fee' => 'required|numeric|min:0',
        ]);

        $transaction = Transaction::create($request->all());

        return response()->json($transaction, 201);
    }

    /**
     * Display the specified transaction.
     *
     * @param \App\Models\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        return response()->json($transaction);
    }

    /**
     * Update the specified transaction in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'from_wallet_id' => 'sometimes|required|integer|exists:wallets,id',
            'to_wallet_id' => 'sometimes|required|integer|exists:wallets,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'currency' => 'sometimes|required|string|max:3',
            'commission_fee' => 'sometimes|required|numeric|min:0',
        ]);

        $transaction->update($request->all());

        return response()->json($transaction);
    }

    /**
     * Remove the specified transaction from storage.
     *
     * @param \App\Models\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(null, 204);
    }


    public function createRequest(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'from_wallet_id' => 'required|integer|exists:wallets,id',
            'to_wallet_id' => 'required|integer|exists:wallets,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Получение кошельков
        $fromWallet = Wallet::find($request->from_wallet_id);
        $toWallet = Wallet::find($request->to_wallet_id);

        // Проверка наличия средств у пользователя
        if ($fromWallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient funds'], 400);
        }

        // Рассчитываем комиссию системы и сумму для получателя
        $systemFee = $request->amount * 0.02;
        $receiverAmount = $request->amount - $systemFee;

        // Создание транзакции
        $transaction = new Transaction([
            'from_wallet_id' => $request->from_wallet_id,
            'to_wallet_id' => $request->to_wallet_id,
            'amount' => $receiverAmount,
            'currency' => $fromWallet->currency,
            'system_fee' => $systemFee,
        ]);

        // Сохранение транзакции и обновление балансов кошельков
        $transaction->save();
        $fromWallet->decrement('balance', $request->amount);
        $toWallet->increment('balance', $receiverAmount);

        return new TransactionResource($transaction);
    }


    public function filter(Request $request)
    {
        $query = Transaction::query();

        // Фильтрация по пользователю
        if ($request->has('user_id')) {
            $query->whereHas('fromWallet', function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }

        // Фильтрация по валюте
        if ($request->has('currency')) {
            $query->where('currency', $request->currency);
        }

        // Фильтрация по интервалу дат
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }

        $transactions = $query->get();

        return TransactionResource::collection($transactions);
    }


    public function executeRequest(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'transaction_id' => 'required|integer|exists:transactions,id',
            'buyer_wallet_id' => 'required|integer|exists:wallets,id',
        ]);

        // Получение транзакции и кошельков
        $transaction = Transaction::find($request->transaction_id);
        $sellerWallet = Wallet::find($transaction->from_wallet_id);
        $buyerWallet = Wallet::find($request->buyer_wallet_id);

        // Проверка наличия средств на кошельке покупателя
        $requiredAmount = $transaction->amount + $transaction->system_fee;
        if ($buyerWallet->balance < $requiredAmount) {
            return response()->json(['message' => 'Insufficient funds'], 400);
        }

        // Совершение обмена средствами и обновление балансов кошельков
        $sellerWallet->increment('balance', $transaction->amount);
        $buyerWallet->decrement('balance', $requiredAmount);

        // Установка статуса транзакции на выполненную (например, 'completed') и сохранение изменений
        $transaction->status = 'completed';
        $transaction->save();

        return new TransactionResource($transaction);
    }



    public function getSystemFees(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        // Получение суммы комиссий системы за указанный интервал дат и группировка по валюте
        $systemFees = Transaction::select('currency', DB::raw('SUM(system_fee) as total_fee'))
            ->whereBetween('created_at', [$request->date_from, $request->date_to])
            ->groupBy('currency')
            ->get();

        return response()->json($systemFees);
    }


}
