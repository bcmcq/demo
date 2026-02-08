<?php

namespace Database\Factories;

use App\Enums\ContentGenerationStatus;
use App\Models\ContentGenerationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContentGenerationRequest>
 */
class ContentGenerationRequestFactory extends Factory
{
    protected $model = ContentGenerationRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => 1,
            'type' => fake()->randomElement(['rewrite', 'generate']),
            'prompt' => fake()->sentence(),
            'platform' => fake()->randomElement(['twitter', 'instagram', 'facebook', 'linkedin']),
            'tone' => fake()->randomElement(['professional', 'casual', 'humorous', 'formal']),
            'status' => ContentGenerationStatus::Pending,
        ];
    }

    /**
     * Set the request as completed with generated content.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentGenerationStatus::Completed,
            'generated_content' => [
                fake()->paragraph(),
                fake()->paragraph(),
                fake()->paragraph(),
            ],
        ]);
    }

    /**
     * Set the request as processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentGenerationStatus::Processing,
        ]);
    }

    /**
     * Set the request as failed with an error message.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentGenerationStatus::Failed,
            'error' => 'LLM request failed: API rate limit exceeded.',
        ]);
    }
}
