<?php

namespace Database\Factories;

use App\Models\Home;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Home>
 */
class HomeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // More bedrooms = higher price, plus some randomness, rounded to the nearest 1,000
        $bedrooms = fake()->numberBetween(2, 5);
        $price = 220000 + ($bedrooms * 60000) + fake()->numberBetween(0, 90000);
    
        return [
            'address'  => fake()->buildingNumber() . ' ' . fake()->streetName(),
            'price'    => round($price, -3),
            'bedrooms' => $bedrooms,
            // community_id is set by the seeder
        ];
    }
}
