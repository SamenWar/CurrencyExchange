<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Display a listing of the wallets.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $wallets = Wallet::all();
        return WalletResource::collection($wallets);
    }

    /**
     * Store a newly created wallet in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'currency' => 'required|string|max:3',
            'balance' => 'required|numeric|min:0',
        ]);

        $wallet = Wallet::create($request->all());

        return WalletResource::collection($wallet);
    }

    /**
     * Display the specified wallet.
     *
     * @param  \App\Models\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function show(Wallet $wallet)
    {
        return WalletResource::collection($wallet);
    }

    /**
     * Update the specified wallet in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Wallet $wallet)
    {
        $request->validate([
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'currency' => 'sometimes|required|string|max:3',
            'balance' => 'sometimes|required|numeric|min:0',
        ]);

        $wallet->update($request->all());

        return WalletResource::collection($wallet);    }

    /**
     * Remove the specified wallet from storage.
     *
     * @param  \App\Models\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Wallet $wallet)
    {
        $wallet->delete();
        return WalletResource::collection($wallet);    }
}
