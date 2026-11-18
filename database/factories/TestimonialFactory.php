<?php

namespace Database\Factories;

use App\Enums\TestimonialSource;
use App\Enums\TestimonialStatus;
use App\Models\Testimonial;
use App\Models\Wall;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'wall_id' => Wall::factory(),
            'status' => TestimonialStatus::Pending,
            'source' => TestimonialSource::Form,
            'rating' => fake()->numberBetween(4, 5),
            'body' => fake()->paragraph(),
            'author_name' => fake()->name(),
            'author_title' => fake()->jobTitle().' at '.fake()->company(),
            'author_email' => fake()->safeEmail(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => TestimonialStatus::Approved,
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => TestimonialStatus::Rejected]);
    }

    public function featured(): static
    {
        return $this->approved()->state(fn () => ['featured' => true]);
    }
}
