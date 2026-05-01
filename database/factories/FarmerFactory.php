<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class FarmerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'identifier'   => 'AGR-' . $this->faker->unique()->numerify('###'),
            'firstname'    => $this->faker->firstName(),
            'lastname'     => $this->faker->lastName(),
            'phone'        => $this->faker->unique()->numerify('07########'),
            'credit_limit' => $this->faker->randomElement([50000, 100000, 200000]),
        ];
    }
}