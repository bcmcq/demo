<?php

namespace Tests\Feature;

use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class RequestTimingMiddlewareTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('app.request_timing', true);
    }

    public function test_response_includes_request_duration_header(): void
    {
        $response = $this->getJson('/api/social_media_contents');

        $response->assertOk();
        $this->assertTrue($response->headers->has('X-Request-Duration-Ms'));
        $this->assertGreaterThan(0, (float) $response->headers->get('X-Request-Duration-Ms'));
    }

    public function test_response_includes_query_count_header(): void
    {
        $response = $this->getJson('/api/social_media_contents');

        $response->assertOk();
        $this->assertTrue($response->headers->has('X-Query-Count'));
        $this->assertGreaterThanOrEqual(0, (int) $response->headers->get('X-Query-Count'));
    }

    public function test_response_includes_db_time_header(): void
    {
        $response = $this->getJson('/api/social_media_contents');

        $response->assertOk();
        $this->assertTrue($response->headers->has('X-DB-Time-Ms'));
        $this->assertGreaterThanOrEqual(0, (float) $response->headers->get('X-DB-Time-Ms'));
    }

    public function test_headers_present_on_post_requests(): void
    {
        $category = SocialMediaCategory::factory()->create();

        $response = $this->postJson('/api/social_media_contents', [
            'social_media_category_id' => $category->id,
            'title' => 'Test Content',
            'content' => 'Test body.',
        ]);

        $response->assertStatus(201);
        $this->assertTrue($response->headers->has('X-Request-Duration-Ms'));
        $this->assertTrue($response->headers->has('X-Query-Count'));
        $this->assertTrue($response->headers->has('X-DB-Time-Ms'));
    }

    public function test_headers_present_on_error_responses(): void
    {
        $response = $this->getJson('/api/social_media_contents/9999');

        $response->assertNotFound();
        $this->assertTrue($response->headers->has('X-Request-Duration-Ms'));
        $this->assertTrue($response->headers->has('X-Query-Count'));
    }

    public function test_query_count_reflects_actual_queries(): void
    {
        $category = SocialMediaCategory::factory()->create();

        SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/social_media_contents');

        $response->assertOk();
        $queryCount = (int) $response->headers->get('X-Query-Count');
        $this->assertGreaterThanOrEqual(2, $queryCount, 'Index should run at least a count + paginate query');
    }

    public function test_logs_structured_request_metrics(): void
    {
        Log::spy();

        $this->getJson('/api/social_media_contents');

        Log::shouldHaveReceived('info')
            ->withArgs(function (string $message, array $context) {
                return $message === 'API request'
                    && $context['method'] === 'GET'
                    && $context['uri'] === '/api/social_media_contents'
                    && $context['status'] === 200
                    && isset($context['duration_ms'])
                    && isset($context['query_count'])
                    && isset($context['db_time_ms'])
                    && isset($context['memory_peak_mb']);
            })
            ->once();
    }

    public function test_normal_request_does_not_log_warning(): void
    {
        Log::spy();

        $this->getJson('/api/social_media_contents');

        Log::shouldNotHaveReceived('warning');
    }

    public function test_duration_header_is_numeric(): void
    {
        $response = $this->getJson('/api/social_media_contents');

        $response->assertOk();
        $this->assertIsNumeric($response->headers->get('X-Request-Duration-Ms'));
        $this->assertIsNumeric($response->headers->get('X-Query-Count'));
        $this->assertIsNumeric($response->headers->get('X-DB-Time-Ms'));
    }

    public function test_headers_not_present_when_disabled(): void
    {
        Config::set('app.request_timing', false);

        $response = $this->getJson('/api/social_media_contents');

        $response->assertOk();
        $this->assertFalse($response->headers->has('X-Request-Duration-Ms'));
        $this->assertFalse($response->headers->has('X-Query-Count'));
        $this->assertFalse($response->headers->has('X-DB-Time-Ms'));
    }

    public function test_no_logging_when_disabled(): void
    {
        Config::set('app.request_timing', false);
        Log::spy();

        $this->getJson('/api/social_media_contents');

        Log::shouldNotHaveReceived('info');
        Log::shouldNotHaveReceived('warning');
    }
}
