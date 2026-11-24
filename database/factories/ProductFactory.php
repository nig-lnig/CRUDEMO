<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->words(3, true)),
            'sku' => strtoupper($this->faker->unique()->bothify('TST-####-??')),
            'category' => $this->faker->randomElement(['Informatique', 'Audio', 'Accessoires', 'Mobilier']),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 5, 1500),
            'stock' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
