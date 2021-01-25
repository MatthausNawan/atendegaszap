<?php

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            [
                'description' => 'Agua 20L',
                'price' => 8.00
            ],
            [
                'description' => 'Bot gás 13kg p-13',
                'price' => 75.00
            ],
            [
                'description' => 'Bot gás 8kg p-6',
                'price' => 55.00
            ],
        ];

        Product::insert($products);
    }
}
