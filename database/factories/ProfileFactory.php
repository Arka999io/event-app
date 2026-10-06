<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'birth_date' => fake()->dateTimeBetween('-50 years', '-18 years'),
            'gender' => fake()->randomElement(Gender::cases()),
            'bio' => fake()->optional()->paragraph(),
            'city' => fake()->city(),
        ];
    }
}
