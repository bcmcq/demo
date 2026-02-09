<?php

namespace Database\Factories;

use App\Models\SocialMediaCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialMediaCategory>
 */
class SocialMediaCategoryFactory extends Factory
{
    protected $model = SocialMediaCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->slug(2),
        ];
    }
}
