<?php

namespace Database\Factories;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state (image).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'social_media_content_id' => null,
            'type' => MediaType::Image,
            'file_path' => 'images/'.fake()->uuid().'.jpg',
            'file_name' => fake()->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(10000, 2000000),
        ];
    }

    /**
     * Indicate that the media is a video with Mux fields populated.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MediaType::Video,
            'file_path' => 'videos/'.fake()->uuid().'.mp4',
            'file_name' => fake()->word().'.mp4',
            'mime_type' => 'video/mp4',
            'size' => fake()->numberBetween(1000000, 100000000),
            'mux_asset_id' => 'asset_'.fake()->uuid(),
            'mux_playback_id' => 'playback_'.fake()->uuid(),
            'mux_upload_id' => 'upload_'.fake()->uuid(),
            'thumbnail_path' => 'thumbnails/'.fake()->uuid().'.jpg',
        ]);
    }
}
