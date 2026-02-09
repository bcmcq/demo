<?php

namespace App\Services;

use App\Models\SocialMediaContent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutopostLoggerService
{
    private string $requestId;

    public function __construct()
    {
        $this->requestId = Str::uuid()->toString();
    }

    /**
     * Log that no category weights were found for the account.
     */
    public function noWeights(int $accountId): void
    {
        $this->log('no_weights', [
            'account_id' => $accountId,
            'reason' => 'No category weights configured for account',
        ]);
    }

    /**
     * Log successfully loaded category weights.
     *
     * @param  Collection<int, array{category_id: int, weight: int}>  $weights
     */
    public function weightsLoaded(int $accountId, Collection $weights): void
    {
        $this->log('weights_loaded', [
            'account_id' => $accountId,
            'weights' => $weights->toArray(),
            'total_weight' => $weights->sum('weight'),
            'category_count' => $weights->count(),
        ]);
    }

    /**
     * Log the category selected by cumulative weight distribution.
     */
    public function categoryPicked(int $categoryId, int $randomValue, int $totalWeight, int $cumulativeAtPick): void
    {
        $this->log('category_picked', [
            'category_id' => $categoryId,
            'random_value' => $randomValue,
            'total_weight' => $totalWeight,
            'cumulative_at_pick' => $cumulativeAtPick,
        ]);
    }

    /**
     * Log the content item that was selected for autopost.
     */
    public function contentSelected(int $accountId, SocialMediaContent $content, int $categoryId): void
    {
        $this->log('content_selected', [
            'account_id' => $accountId,
            'content_id' => $content->id,
            'content_title' => $content->title,
            'category_id' => $categoryId,
        ]);
    }

    /**
     * Log that a category was skipped due to no available content.
     */
    public function categorySkipped(int $accountId, int $categoryId, int $remainingCategories): void
    {
        $this->log('category_skipped', [
            'account_id' => $accountId,
            'category_id' => $categoryId,
            'reason' => 'No available content in category',
            'remaining_categories' => $remainingCategories,
        ]);
    }

    /**
     * Log that all weighted categories have been exhausted.
     */
    public function allCategoriesExhausted(int $accountId): void
    {
        $this->log('all_categories_exhausted', [
            'account_id' => $accountId,
            'reason' => 'All weighted categories have been exhausted with no available content',
        ]);
    }

    /**
     * Write a structured log entry to the autopost channel.
     *
     * @param  array<string, mixed>  $context
     */
    private function log(string $event, array $context): void
    {
        Log::channel('autopost')->info($event, [
            'request_id' => $this->requestId,
            ...$context,
        ]);
    }
}
