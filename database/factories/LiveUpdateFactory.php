<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LiveUpdate>
 */
class LiveUpdateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'title' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'is_pinned' => fake()->boolean(10),
            'author_id' => User::factory(),
        ];
    }
}
