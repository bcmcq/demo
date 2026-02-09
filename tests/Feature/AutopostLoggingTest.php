<?php

namespace Tests\Feature;

use App\Models\SocialMediaAccountCategoryWeight;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use App\Models\SocialMediaPost;
use App\Services\AutopostService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AutopostLoggingTest extends BaseTestCase
{
    private SocialMediaCategory $holidays;

    private SocialMediaCategory $trivia;

    private string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->holidays = SocialMediaCategory::factory()->create(['name' => 'holidays']);
        $this->trivia = SocialMediaCategory::factory()->create(['name' => 'trivia']);

        $this->logPath = storage_path('logs/autopost-test.log');

        Config::set('logging.channels.autopost', [
            'driver' => 'single',
            'path' => $this->logPath,
            'level' => 'debug',
            'formatter' => \Monolog\Formatter\JsonFormatter::class,
        ]);

        if (file_exists($this->logPath)) {
            unlink($this->logPath);
        }

        Log::forgetChannel('autopost');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logPath)) {
            unlink($this->logPath);
        }

        parent::tearDown();
    }

    public function test_logs_weights_loaded_on_successful_selection(): void
    {
        SocialMediaAccountCategoryWeight::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 10,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Holiday Post',
        ]);

        $service = new AutopostService;
        $service->selectContent($this->account->id);

        $entries = $this->getLogEntries();
        $weightsLoaded = $this->findLogEntry($entries, 'weights_loaded');

        $this->assertNotNull($weightsLoaded, 'Expected weights_loaded log entry');
        $this->assertEquals($this->account->id, $weightsLoaded['context']['account_id']);
        $this->assertEquals(10, $weightsLoaded['context']['total_weight']);
        $this->assertEquals(1, $weightsLoaded['context']['category_count']);
        $this->assertArrayHasKey('request_id', $weightsLoaded['context']);
    }

    public function test_logs_content_selected_with_content_details(): void
    {
        SocialMediaAccountCategoryWeight::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 10,
        ]);

        $content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Holiday Post',
        ]);

        $service = new AutopostService;
        $service->selectContent($this->account->id);

        $entries = $this->getLogEntries();
        $selected = $this->findLogEntry($entries, 'content_selected');

        $this->assertNotNull($selected, 'Expected content_selected log entry');
        $this->assertEquals($content->id, $selected['context']['content_id']);
        $this->assertEquals('Holiday Post', $selected['context']['content_title']);
        $this->assertEquals($this->holidays->id, $selected['context']['category_id']);
    }

    public function test_logs_category_picked_with_distribution_info(): void
    {
        SocialMediaAccountCategoryWeight::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 10,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Holiday Post',
        ]);

        $service = new AutopostService;
        $service->selectContent($this->account->id);

        $entries = $this->getLogEntries();
        $picked = $this->findLogEntry($entries, 'category_picked');

        $this->assertNotNull($picked, 'Expected category_picked log entry');
        $this->assertEquals($this->holidays->id, $picked['context']['category_id']);
        $this->assertArrayHasKey('random_value', $picked['context']);
        $this->assertEquals(10, $picked['context']['total_weight']);
    }

    public function test_logs_no_weights_when_account_has_none(): void
    {
        $service = new AutopostService;
        $service->selectContent($this->account->id);

        $entries = $this->getLogEntries();
        $noWeights = $this->findLogEntry($entries, 'no_weights');

        $this->assertNotNull($noWeights, 'Expected no_weights log entry');
        $this->assertEquals($this->account->id, $noWeights['context']['account_id']);
    }

    public function test_logs_category_skipped_when_no_available_content(): void
    {
        SocialMediaAccountCategoryWeight::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 10,
        ]);

        SocialMediaAccountCategoryWeight::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->trivia->id,
            'weight' => 5,
        ]);

        $postedContent = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Posted Holiday',
        ]);

        SocialMediaPost::factory()->create([
            'account_id' => $this->account->id,
            'social_media_content_id' => $postedContent->id,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->trivia->id,
            'title' => 'Trivia Content',
        ]);

        $service = new AutopostService;
        $service->selectContent($this->account->id, randomValue: 1);

        $entries = $this->getLogEntries();
        $skipped = $this->findLogEntry($entries, 'category_skipped');

        $this->assertNotNull($skipped, 'Expected category_skipped log entry');
        $this->assertEquals($this->holidays->id, $skipped['context']['category_id']);
        $this->assertEquals('No available content in category', $skipped['context']['reason']);
        $this->assertEquals(1, $skipped['context']['remaining_categories']);
    }

    public function test_logs_all_categories_exhausted_when_no_content_available(): void
    {
        SocialMediaAccountCategoryWeight::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 10,
        ]);

        $service = new AutopostService;
        $service->selectContent($this->account->id);

        $entries = $this->getLogEntries();
        $exhausted = $this->findLogEntry($entries, 'all_categories_exhausted');

        $this->assertNotNull($exhausted, 'Expected all_categories_exhausted log entry');
        $this->assertEquals($this->account->id, $exhausted['context']['account_id']);
    }

    public function test_all_log_entries_share_same_request_id(): void
    {
        SocialMediaAccountCategoryWeight::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'weight' => 10,
        ]);

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->holidays->id,
            'title' => 'Holiday Post',
        ]);

        $service = new AutopostService;
        $service->selectContent($this->account->id);

        $entries = $this->getLogEntries();
        $requestIds = array_unique(array_column(array_column($entries, 'context'), 'request_id'));

        $this->assertCount(1, $requestIds, 'All log entries should share the same request_id');
    }

    /**
     * Parse all JSON log entries from the test autopost log file.
     *
     * @return array<int, array{message: string, context: array<string, mixed>}>
     */
    private function getLogEntries(): array
    {
        $this->assertFileExists($this->logPath, 'Autopost log file should exist after service call');

        $lines = array_filter(explode("\n", file_get_contents($this->logPath)));

        return array_map(fn (string $line) => json_decode($line, true), $lines);
    }

    /**
     * Find the first log entry matching the given message.
     *
     * @param  array<int, array{message: string, context: array<string, mixed>}>  $entries
     * @return array{message: string, context: array<string, mixed>}|null
     */
    private function findLogEntry(array $entries, string $message): ?array
    {
        foreach ($entries as $entry) {
            if (($entry['message'] ?? null) === $message) {
                return $entry;
            }
        }

        return null;
    }
}
