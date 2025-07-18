<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::create(['name' => 'Ноутбук', 'price' => 95000.00]);
        Product::create(['name' => 'Компьютерная мышь', 'price' => 2500.50]);
        Product::create(['name' => 'Клавиатура', 'price' => 4999.99]);
        Product::create(['name' => 'Монитор', 'price' => 25000.00]);
    }
}
