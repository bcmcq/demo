<?php

namespace Database\Seeders;

use App\Models\SocialMediaCategory;
use Illuminate\Database\Seeder;

class SocialMediaCategorySeeder extends Seeder
{
    protected array $names = [
        'holidays',
        'seasons',
        'trivia',
        'current_events',
        'facts',
        'funny_videos',
        'jokes',
        'news',
        'survey_published',
        'review_featured',
        'review_published',
    ];

    /**
     * Run the database seeds.
     */
    public function run()
    {
        foreach ($this->names as $name) {
            SocialMediaCategory::create(compact('name'));
        }
    }
}
