<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Enums\GenderPreference;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organizer_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'starts_at' => fake()->dateTimeBetween('+1 day', '+1 month'),
            'ends_at' => null,
            'meeting_address' => fake()->optional()->address(),
            'venue_address' => fake()->address(),
            'min_age' => null,
            'max_age' => null,
            'gender_preference' => GenderPreference::Any,
            'spots_total' => fake()->numberBetween(2, 20),
            'spots_taken' => 0,
            'status' => EventStatus::Published,
            'published_at' => now(),
        ];
    }

        public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Completed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Cancelled,
        ]);
    }
}