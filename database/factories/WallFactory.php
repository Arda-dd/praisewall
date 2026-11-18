<?php

namespace Database\Factories;

use App\Models\Wall;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Wall>
 */
class WallFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'headline' => 'Share your experience with '.$name,
            'prompt' => 'What did you like the most? How did it help you?',
            'accent_color' => '#6d28d9',
            'thank_you_message' => 'Thank you! Your testimonial will appear once it has been reviewed.',
            'collecting' => true,
            'ask_for_rating' => true,
            'ask_for_avatar' => true,
        ];
    }
}
