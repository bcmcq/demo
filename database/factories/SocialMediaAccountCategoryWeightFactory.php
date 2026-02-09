<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\SocialMediaAccountCategoryWeight;
use App\Models\SocialMediaCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialMediaAccountCategoryWeight>
 */
class SocialMediaAccountCategoryWeightFactory extends Factory
{
    protected $model = SocialMediaAccountCategoryWeight::class;

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
            'weight' => fake()->numberBetween(1, 10),
        ];
    }
}
