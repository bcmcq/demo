<?php

namespace Tests\Feature;

use App\Http\Middleware\RequestTiming;
use App\Models\Media;
use App\Models\SocialMediaAccountCategoryWeight;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use App\Models\SocialMediaPost;
use App\Models\SocialMediaSchedule;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\DB;

/**
 * Performance profiling test that captures query counts and response times
 * for key API endpoints. Run before and after optimizations to measure impact.
 *
 * Usage: vendor/bin/sail artisan test tests/Feature/EndpointPerformanceTest.php
 */
#[\PHPUnit\Framework\Attributes\Group('performance')]
class EndpointPerformanceTest extends BaseTestCase
{
    private SocialMediaCategory $holidays;

    private SocialMediaCategory $trivia;

    private SocialMediaCategory $news;

    private SocialMediaCategory $tips;

    private SocialMediaCategory $quotes;

    /** @var array<string, array{query_count: int, duration_ms: float, memory_kb: int}> */
    private array $metrics = [];

    /**
     * Maximum allowed query counts per endpoint.
     * If a code change causes queries to exceed these ceilings, the test fails.
     * Update these thresholds intentionally when optimizations reduce query counts.
     *
     * @var array<string, int>
     */
    private const QUERY_CEILINGS = [
        'content_index' => 2,
        'content_index_with_includes' => 6,
        'content_show' => 6,
        'autopost' => 3,
        'categories_index' => 2,
        'posts_index' => 2,
        'posts_index_with_includes' => 3,
        'media_index' => 2,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(RequestTiming::class);

        $this->holidays = SocialMediaCategory::factory()->create(['name' => 'holidays']);
        $this->trivia = SocialMediaCategory::factory()->create(['name' => 'trivia']);
        $this->news = SocialMediaCategory::factory()->create(['name' => 'news']);
        $this->tips = SocialMediaCategory::factory()->create(['name' => 'tips']);
        $this->quotes = SocialMediaCategory::factory()->create(['name' => 'quotes']);

        $this->seedRealisticData();
    }

    public function test_content_index_query_count(): void
    {
        $metrics = $this->profileEndpoint('GET /api/social_media_contents', function () {
            return $this->getJson('/api/social_media_contents');
        });

        $metrics['response']->assertOk();

        $this->addMetric('content_index', $metrics);
        $this->outputMetric('GET /api/social_media_contents (index)', $metrics);
    }

    public function test_content_index_with_includes_query_count(): void
    {
        $metrics = $this->profileEndpoint('GET /api/social_media_contents?include=category,posts,schedules,media', function () {
            return $this->getJson('/api/social_media_contents?include=category,posts,schedules,media');
        });

        $metrics['response']->assertOk();

        $this->addMetric('content_index_with_includes', $metrics);
        $this->outputMetric('GET /api/social_media_contents (index + includes)', $metrics);
    }

    public function test_content_show_query_count(): void
    {
        $content = SocialMediaContent::query()
            ->where('account_id', $this->account->id)
            ->first();

        $metrics = $this->profileEndpoint('GET /api/social_media_contents/{id}', function () use ($content) {
            return $this->getJson('/api/social_media_contents/'.$content->id);
        });

        $metrics['response']->assertOk();

        $this->addMetric('content_show', $metrics);
        $this->outputMetric('GET /api/social_media_contents/{id} (show)', $metrics);
    }

    public function test_autopost_query_count(): void
    {
        $metrics = $this->profileEndpoint('GET /api/social_media_contents/autopost', function () {
            return $this->getJson('/api/social_media_contents/autopost');
        });

        $metrics['response']->assertOk();

        $this->addMetric('autopost', $metrics);
        $this->outputMetric('GET /api/social_media_contents/autopost', $metrics);
    }

    public function test_categories_index_query_count(): void
    {
        $metrics = $this->profileEndpoint('GET /api/social_media_categories', function () {
            return $this->getJson('/api/social_media_categories');
        });

        $metrics['response']->assertOk();

        $this->addMetric('categories_index', $metrics);
        $this->outputMetric('GET /api/social_media_categories (index)', $metrics);
    }

    public function test_posts_index_query_count(): void
    {
        $metrics = $this->profileEndpoint('GET /api/social_media_posts', function () {
            return $this->getJson('/api/social_media_posts');
        });

        $metrics['response']->assertOk();

        $this->addMetric('posts_index', $metrics);
        $this->outputMetric('GET /api/social_media_posts (index)', $metrics);
    }

    public function test_posts_index_with_includes_query_count(): void
    {
        $metrics = $this->profileEndpoint('GET /api/social_media_posts?include=content', function () {
            return $this->getJson('/api/social_media_posts?include=content');
        });

        $metrics['response']->assertOk();

        $this->addMetric('posts_index_with_includes', $metrics);
        $this->outputMetric('GET /api/social_media_posts (index + includes)', $metrics);
    }

    public function test_media_index_query_count(): void
    {
        $content = SocialMediaContent::query()
            ->where('account_id', $this->account->id)
            ->whereHas('media')
            ->first();

        $metrics = $this->profileEndpoint('GET /api/social_media_contents/{id}/media', function () use ($content) {
            return $this->getJson('/api/social_media_contents/'.$content->id.'/media');
        });

        $metrics['response']->assertOk();

        $this->addMetric('media_index', $metrics);
        $this->outputMetric('GET /api/social_media_contents/{id}/media (media index)', $metrics);
    }

    /**
     * Profile a single endpoint call and return metrics.
     *
     * @return array{query_count: int, duration_ms: float, memory_kb: int, queries: array<int, array<string, mixed>>, response: \Illuminate\Testing\TestResponse}
     */
    private function profileEndpoint(string $label, callable $callback): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $memoryBefore = memory_get_usage(true);
        $startTime = hrtime(true);

        $response = $callback();

        $endTime = hrtime(true);
        $memoryAfter = memory_get_peak_usage(true);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $durationMs = ($endTime - $startTime) / 1_000_000;
        $totalDbTimeMs = collect($queries)->sum('time');

        return [
            'query_count' => count($queries),
            'duration_ms' => round($durationMs, 2),
            'db_time_ms' => round($totalDbTimeMs, 2),
            'memory_kb' => (int) round(($memoryAfter - $memoryBefore) / 1024),
            'queries' => $queries,
            'response' => $response,
        ];
    }

    /**
     * Store metrics and assert query count stays within the allowed ceiling.
     *
     * @param  array{query_count: int, duration_ms: float, db_time_ms: float, memory_kb: int}  $metrics
     */
    private function addMetric(string $key, array $metrics): void
    {
        $this->metrics[$key] = [
            'query_count' => $metrics['query_count'],
            'duration_ms' => $metrics['duration_ms'],
            'db_time_ms' => $metrics['db_time_ms'],
            'memory_kb' => $metrics['memory_kb'],
        ];

        if (isset(self::QUERY_CEILINGS[$key])) {
            $ceiling = self::QUERY_CEILINGS[$key];
            $this->assertLessThanOrEqual(
                $ceiling,
                $metrics['query_count'],
                "Endpoint '{$key}' executed {$metrics['query_count']} queries, exceeding the ceiling of {$ceiling}. "
                    .'This may indicate an N+1 regression. Update QUERY_CEILINGS intentionally if this is expected.'
            );
        }
    }

    /**
     * Output a formatted metric line to the test console.
     *
     * @param  array{query_count: int, duration_ms: float, db_time_ms: float, memory_kb: int, queries: array<int, array<string, mixed>>}  $metrics
     */
    private function outputMetric(string $label, array $metrics): void
    {
        $output = sprintf(
            "\n  [PERF] %-55s | Queries: %3d | DB: %7.2fms | Total: %7.2fms | Mem: %5dKB",
            $label,
            $metrics['query_count'],
            $metrics['db_time_ms'],
            $metrics['duration_ms'],
            $metrics['memory_kb'],
        );

        fwrite(STDERR, $output);

        if ($metrics['query_count'] > 10) {
            fwrite(STDERR, ' ⚠ HIGH QUERY COUNT');
        }

        fwrite(STDERR, "\n");

        foreach ($metrics['queries'] as $index => $query) {
            $queryNum = $index + 1;
            $time = round($query['time'], 2);
            $sql = $query['query'];

            if (strlen($sql) > 120) {
                $sql = substr($sql, 0, 117).'...';
            }

            fwrite(STDERR, sprintf("         #%02d [%5.2fms] %s\n", $queryNum, $time, $sql));
        }
    }

    /**
     * Seed a stress-test dataset for profiling.
     *
     * Creates: 1000 content items across 5 categories, 400 posts, 200 schedules,
     * 250 media items (spread across content), and 5 category weights.
     * This volume makes index impact clearly measurable.
     */
    private function seedRealisticData(): void
    {
        $categories = [$this->holidays, $this->trivia, $this->news, $this->tips, $this->quotes];

        foreach ($categories as $index => $category) {
            SocialMediaAccountCategoryWeight::factory()->create([
                'account_id' => $this->account->id,
                'social_media_category_id' => $category->id,
                'weight' => ($index + 1) * 2,
            ]);
        }

        $contentItems = SocialMediaContent::factory()
            ->count(1000)
            ->sequence(fn (Sequence $sequence) => [
                'social_media_category_id' => $categories[$sequence->index % 5]->id,
            ])
            ->create(['account_id' => $this->account->id]);

        $contentItems->take(400)->each(fn (SocialMediaContent $content, int $i) => SocialMediaPost::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $content->id,
            'posted_at' => now()->subDays(400 - $i),
        ]));

        $contentItems->slice(400, 200)->values()->each(fn (SocialMediaContent $content, int $i) => SocialMediaSchedule::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $content->id,
            'scheduled_at' => now()->addDays($i + 1),
        ]));

        $contentItems->take(100)->each(fn (SocialMediaContent $content) => Media::factory()->count(2)->create([
            'social_media_content_id' => $content->id,
        ]));

        Media::factory()->count(50)->sequence(fn (Sequence $sequence) => [
            'social_media_content_id' => $contentItems[$sequence->index % 100]->id,
        ])->create();
    }
}
