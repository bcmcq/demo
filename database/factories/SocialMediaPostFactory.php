<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\SocialMediaContent;
use App\Models\SocialMediaPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialMediaPost>
 */
class SocialMediaPostFactory extends Factory
{
    protected $model = SocialMediaPost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'social_media_content_id' => SocialMediaContent::factory(),
            'posted_at' => fake()->dateTimeBetween('-6 months'),
        ];
    }
}
