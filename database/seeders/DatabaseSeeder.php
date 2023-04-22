<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Создаем пользователей
        $users = User::factory()->count(5)->create();

        // Создаем кошельки для пользователей
        $users->each(function ($user) {
            Wallet::factory()->count(2)->create(['user_id' => $user->id]);
        });

        // Создаем транзакции между кошельками пользователей
        $wallets = Wallet::all();
        $transactions = Transaction::factory()->count(10)->make()
            ->each(function ($transaction) use ($wallets) {
                $transaction->from_wallet_id = $wallets->random()->id;
                $transaction->to_wallet_id = $wallets->random()->id;
                $transaction->save();
            });
    }
}
