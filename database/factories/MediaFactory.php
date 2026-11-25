<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['anime', 'drama']);

        return [
            'title' => fake()->unique()->sentence(3),
            'type' => $type,
            'genre' => fake()->randomElement(['Fantasy', 'Romance', 'Action', 'Sports', 'Thriller', 'Slice of Life']),
            'year' => fake()->numberBetween(2010, 2026),
            'episodes' => fake()->numberBetween(12, 50),
            'country' => $type === 'drama' ? fake()->randomElement(['Korea Selatan', 'Jepang', 'Indonesia']) : null,
        ];
    }
}
