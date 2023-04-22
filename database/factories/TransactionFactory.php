<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $fromWallet = Wallet::factory();
        $toWallet = Wallet::factory();

        return [
            'from_wallet_id' => $fromWallet,
            'to_wallet_id' => $toWallet,
            'amount' => $this->faker->randomFloat(2, 0, 5000),
            'currency' => $this->faker->randomElement(['USD', 'UAH', 'EUR']),
            'commission_fee' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
