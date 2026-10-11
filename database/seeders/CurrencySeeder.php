<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'rate_to_usd' => 1.0000],
            ['code' => 'EUR', 'name' => 'Euro', 'rate_to_usd' => 1.1000],
            ['code' => 'GBP', 'name' => 'British Pound', 'rate_to_usd' => 1.2500],
            ['code' => 'BTC', 'name' => 'Bitcoin', 'rate_to_usd' => 30000.0000],
            ['code' => 'ETH', 'name' => 'Ethereum', 'rate_to_usd' => 2000.0000],
            ['code' => 'USDT', 'name' => 'Tether', 'rate_to_usd' => 1.0000],
            ['code' => 'LBC', 'name' => 'Lebanese Pound', 'rate_to_usd' => 0.000066], // example
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
