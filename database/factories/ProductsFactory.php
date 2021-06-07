<?php

namespace Database\Factories;

use App\Models\Products;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Products::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'productName' => $this->faker->words(3, true),
            'productType' => $this->faker->numberBetween(1, 3),
            'productDescription' => $this->faker->sentence(10, true),
            'productPrice' => $this->faker->randomNumber(5),
            'productImage' => 'images/hWyjPlo1ndkHDREMR7aKucndAN5lJJiM6o96jHZC.jpg',
            'user_id' => $this->faker->numberBetween(1, 10),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
