<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\SocialMediaAccountCategoryWeight;
use App\Models\SocialMediaCategory;
use Illuminate\Database\Seeder;

class SocialMediaCategoryAccountWeightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categories = SocialMediaCategory::all();

        Account::all()->each(function (Account $account) use ($categories) {
            $categories->each(function ($category) use ($account) {
                SocialMediaAccountCategoryWeight::create([
                    'weight' => rand(0, 10),
                    'account_id' => $account->id,

                    'social_media_category_id' => $category->id,
                ]);
            });
        });
    }
}
