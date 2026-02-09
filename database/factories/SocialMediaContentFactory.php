<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialMediaContent>
 */
class SocialMediaContentFactory extends Factory
{
    protected $model = SocialMediaContent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'social_media_category_id' => SocialMediaCategory::factory(),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(2, true),
        ];
    }
}
