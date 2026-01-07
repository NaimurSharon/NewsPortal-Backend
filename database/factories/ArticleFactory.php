<?php

namespace Database\Factories;

use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(8);
        $status = fake()->randomElement(['draft', 'published', 'archived']);
        $published_at = $status === 'published' ? fake()->dateTimeBetween('-1 year', 'now') : null;

        return [
            'title' => $title,
            'subtitle' => fake()->sentence(12),
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'excerpt' => fake()->paragraph(2),
            'content' => collect(fake()->paragraphs(10))->map(fn($p) => "<p>$p</p>")->implode(''),
            'author_id' => User::factory(),
            'section_id' => Section::factory(),
            'format' => fake()->randomElement(['standard', 'video', 'audio', 'gallery', 'live']),
            'type' => fake()->randomElement(['news', 'opinion', 'editorial', 'feature', 'special_report']),
            'status' => $status,
            'published_at' => $published_at,
            'reading_time' => fake()->numberBetween(2, 15),
            'featured_image_url' => 'https://picsum.photos/seed/' . fake()->uuid() . '/1200/800',
            'featured_image_caption' => fake()->sentence(),
            'featured_image_credit' => fake()->name(),
            'is_featured' => fake()->boolean(10),
            'is_exclusive' => fake()->boolean(10),
            'is_live' => false, // Will be set to true for 'live' format in state if needed
            'allow_comments' => true,
            'view_count' => fake()->numberBetween(0, 100000),
            'share_count' => fake()->numberBetween(0, 5000),
        ];
    }

    public function live(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'live',
            'is_live' => true,
        ]);
    }
}
