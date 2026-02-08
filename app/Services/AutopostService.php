<?php

namespace App\Services;

use App\Models\SocialMediaAccountCategoryWeight;
use App\Models\SocialMediaContent;
use Illuminate\Support\Collection;

class AutopostService
{
    /**
     * Select a random piece of content using cumulative weight distribution.
     *
     * Algorithm:
     * 1. Load account's category weights.
     * 2. Build a cumulative weight array.
     * 3. Generate a random number in [1, totalWeight].
     * 4. Find the category whose cumulative range contains that number.
     * 5. Pick a random available (unposted & unscheduled) content item from that category.
     * 6. If the selected category has no available content, remove it and retry.
     * 7. If no categories remain, return null.
     *
     * Selection algo w/ weights example holidays=5, trivia=3, news=2 (total=10):
     *  ┌──────────────┬───────────────────┬─────────────────────────────┐
     *  │ Random Value │ Expected Category │          Boundary           │
     *  ├──────────────┼───────────────────┼─────────────────────────────┤
     *  │ 1            │ holidays          │ first category, lower edge  │
     *  ├──────────────┼───────────────────┼─────────────────────────────┤
     *  │ 5            │ holidays          │ first category, upper edge  │
     *  ├──────────────┼───────────────────┼─────────────────────────────┤
     *  │ 6            │ trivia            │ second category, lower edge │
     *  ├──────────────┼───────────────────┼─────────────────────────────┤
     *  │ 8            │ trivia            │ second category, upper edge │
     *  ├──────────────┼───────────────────┼─────────────────────────────┤
     *  │ 9            │ news              │ third category, lower edge  │
     *  ├──────────────┼───────────────────┼─────────────────────────────┤
     *  │ 10           │ news              │ third category, upper edge  │
     *  └──────────────┴───────────────────┴─────────────────────────────┘
     *
     * @param  ?int  $randomValue  Optional random value to use for the selection, primarily for testing. If not provided, a random value will be generated.
     */
    public function selectContent(int $accountId, ?int $randomValue = null): ?SocialMediaContent
    {
        $weights = $this->loadWeights($accountId);

        if ($weights->isEmpty()) {
            return null;
        }

        return $this->selectFromWeights($accountId, $weights, $randomValue);
    }

    /**
     * Load the category weights for the given account.
     *
     * @return Collection<int, array{category_id: int, weight: int}>
     */
    protected function loadWeights(int $accountId): Collection
    {
        return SocialMediaAccountCategoryWeight::query()
            ->where('account_id', $accountId)
            ->where('weight', '>', 0)
            ->get()
            ->map(fn (SocialMediaAccountCategoryWeight $w) => [
                'category_id' => $w->social_media_category_id,
                'weight' => $w->weight,
            ]);
    }

    /**
     * Recursively select content using cumulative weight with fallback.
     *
     * @param  Collection<int, array{category_id: int, weight: int}>  $weights
     * @param  ?int  $randomValue  Optional random value to use for the selection, primarily for testing. If not provided, a random value will be generated.
     */
    protected function selectFromWeights(int $accountId, Collection $weights, ?int $randomValue = null): ?SocialMediaContent
    {
        if ($weights->isEmpty()) {
            return null;
        }

        $categoryId = $this->pickCategoryByCumulativeWeight($weights, $randomValue);

        $content = $this->findAvailableContent($accountId, $categoryId);

        if ($content !== null) {
            return $content;
        }

        $remaining = $weights->reject(
            fn (array $w) => $w['category_id'] === $categoryId
        )->values();

        return $this->selectFromWeights($accountId, $remaining, $randomValue);
    }

    /**
     * Pick a category ID using cumulative weight distribution.
     *
     * @param  Collection<int, array{category_id: int, weight: int}>  $weights
     * @param  ?int  $randomValue  Optional random value to use for the selection, primarily for testing. If not provided, a random value will be generated.
     */
    protected function pickCategoryByCumulativeWeight(Collection $weights, ?int $randomValue = null): int
    {
        $totalWeight = $weights->sum('weight');
        $random = $randomValue ?? random_int(1, $totalWeight);

        $cumulative = 0;

        foreach ($weights as $entry) {
            $cumulative += $entry['weight'];

            if ($random <= $cumulative) {
                return $entry['category_id'];
            }
        }

        return $weights->last()['category_id'];
    }

    /**
     * Find a random available content item for the given account and category.
     * Available means: not yet posted AND not yet scheduled.
     */
    protected function findAvailableContent(int $accountId, int $categoryId): ?SocialMediaContent
    {
        return SocialMediaContent::query()
            ->where('account_id', $accountId)
            ->where('social_media_category_id', $categoryId)
            ->whereDoesntHave('posts')
            ->whereDoesntHave('schedules')
            ->inRandomOrder()
            ->first();
    }
}
