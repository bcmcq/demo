<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\SocialMediaContent;
use App\Models\SocialMediaSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialMediaSchedule>
 */
class SocialMediaScheduleFactory extends Factory
{
    protected $model = SocialMediaSchedule::class;

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
            'scheduled_at' => fake()->dateTimeBetween('now', '+2 months'),
        ];
    }
}
