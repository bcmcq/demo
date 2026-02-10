<?php

namespace Tests\Feature;

use App\Enums\ContentGenerationStatus;
use App\Enums\Platform;
use App\Enums\Tone;
use App\Jobs\GenerateContentJob;
use App\Models\Account;
use App\Models\ContentGenerationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\StructuredResponseFake;
use Prism\Prism\ValueObjects\Usage;

class GenerateContentTest extends BaseTestCase
{
    private Account $otherAccount;

    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->otherAccount = $this->createOtherAccount();
        $this->otherUser = $this->createUserForAccount($this->otherAccount);
    }

    /** -------- GENERATE: HAPPY PATH -------- */
    public function test_generate_dispatches_job_and_returns_202(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/social_media_contents/generate', [
            'prompt' => 'Write a post about our summer sale',
            'platform' => 'instagram',
            'tone' => 'casual',
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['message', 'generation_request_id']);

        Queue::assertPushed(GenerateContentJob::class, function (GenerateContentJob $job) {
            return $job->contentGenerationRequest->type === 'generate'
                && $job->contentGenerationRequest->prompt === 'Write a post about our summer sale'
                && $job->contentGenerationRequest->platform === Platform::Instagram
                && $job->contentGenerationRequest->tone === Tone::Casual;
        });

        $this->assertDatabaseHas('content_generation_requests', [
            'account_id' => $this->account->id,
            'type' => 'generate',
            'prompt' => 'Write a post about our summer sale',
            'platform' => 'instagram',
            'tone' => 'casual',
            'status' => ContentGenerationStatus::Pending->value,
        ]);
    }

    public function test_generate_job_calls_prism_and_completes(): void
    {
        $variations = [
            'Summer sale is HERE! 🌞🔥 Up to 50% off everything. Shop now! #SummerSale #Deals',
            'Don\'t miss out! Our biggest summer sale is live 🏖️ Save up to 50%! #SummerVibes',
            'Hot deals for hot days! ☀️ Summer sale — 50% off sitewide. Link in bio! #Sale',
        ];

        $fakeResponse = StructuredResponseFake::make()
            ->withStructured(['variations' => $variations])
            ->withUsage(new Usage(40, 80))
            ->withFinishReason(FinishReason::Stop);

        Prism::fake([$fakeResponse]);

        $generationRequest = ContentGenerationRequest::create([
            'account_id' => $this->account->id,
            'type' => 'generate',
            'prompt' => 'Write a post about our summer sale',
            'platform' => 'instagram',
            'tone' => 'casual',
            'status' => ContentGenerationStatus::Pending,
        ]);

        $job = new GenerateContentJob($generationRequest);
        $job->handle(app(\App\Services\AIContentWriterService::class));

        $generationRequest->refresh();

        $this->assertEquals(ContentGenerationStatus::Completed, $generationRequest->status);
        $this->assertIsArray($generationRequest->generated_content);
        $this->assertCount(3, $generationRequest->generated_content);
        $this->assertEquals($variations, $generationRequest->generated_content);
    }

    public function test_generate_job_handles_failure_gracefully(): void
    {
        $generationRequest = ContentGenerationRequest::create([
            'account_id' => $this->account->id,
            'type' => 'generate',
            'prompt' => 'Write a post about our summer sale',
            'platform' => 'instagram',
            'tone' => 'casual',
            'status' => ContentGenerationStatus::Pending,
        ]);

        $job = new GenerateContentJob($generationRequest);
        $job->failed(new \RuntimeException('Connection timed out'));

        $generationRequest->refresh();

        $this->assertEquals(ContentGenerationStatus::Failed, $generationRequest->status);
        $this->assertEquals('Connection timed out', $generationRequest->error);
    }

    /** -------- GENERATE: VALIDATION -------- */
    public function test_generate_requires_prompt(): void
    {
        $response = $this->postJson('/api/social_media_contents/generate', [
            'platform' => 'twitter',
            'tone' => 'casual',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['prompt']);
    }

    public function test_generate_requires_platform(): void
    {
        $response = $this->postJson('/api/social_media_contents/generate', [
            'prompt' => 'Write a post',
            'tone' => 'casual',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['platform']);
    }

    public function test_generate_requires_tone(): void
    {
        $response = $this->postJson('/api/social_media_contents/generate', [
            'prompt' => 'Write a post',
            'platform' => 'twitter',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tone']);
    }

    public function test_generate_rejects_invalid_platform(): void
    {
        $response = $this->postJson('/api/social_media_contents/generate', [
            'prompt' => 'Write a post',
            'platform' => 'myspace',
            'tone' => 'casual',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['platform']);
    }

    public function test_generate_rejects_invalid_tone(): void
    {
        $response = $this->postJson('/api/social_media_contents/generate', [
            'prompt' => 'Write a post',
            'platform' => 'twitter',
            'tone' => 'sarcastic',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tone']);
    }

    public function test_generate_rejects_prompt_exceeding_max_length(): void
    {
        $response = $this->postJson('/api/social_media_contents/generate', [
            'prompt' => str_repeat('a', 1001),
            'platform' => 'twitter',
            'tone' => 'casual',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['prompt']);
    }

    /** -------- STATUS ENDPOINT -------- */
    public function test_status_returns_pending_request(): void
    {
        $generationRequest = ContentGenerationRequest::create([
            'account_id' => $this->account->id,
            'type' => 'generate',
            'prompt' => 'Write a post',
            'platform' => 'twitter',
            'tone' => 'casual',
            'status' => ContentGenerationStatus::Pending,
        ]);

        $response = $this->getJson("/api/social_media_contents/generate/{$generationRequest->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $generationRequest->id,
                    'type' => 'generate',
                    'platform' => 'twitter',
                    'tone' => 'casual',
                    'status' => 'pending',
                    'generated_content' => null,
                    'error' => null,
                ],
            ]);
    }

    public function test_status_returns_processing_request(): void
    {
        $generationRequest = ContentGenerationRequest::create([
            'account_id' => $this->account->id,
            'type' => 'generate',
            'prompt' => 'Write a post',
            'platform' => 'twitter',
            'tone' => 'casual',
            'status' => ContentGenerationStatus::Processing,
        ]);

        $response = $this->getJson("/api/social_media_contents/generate/{$generationRequest->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'status' => 'processing',
                    'generated_content' => null,
                    'error' => null,
                ],
            ]);
    }

    public function test_status_returns_completed_request_with_content(): void
    {
        $variations = [
            'Check out our latest tweet!',
            'Big news dropping today!',
            'You won\'t want to miss this!',
        ];

        $generationRequest = ContentGenerationRequest::create([
            'account_id' => $this->account->id,
            'type' => 'generate',
            'prompt' => 'Write a post',
            'platform' => 'twitter',
            'tone' => 'casual',
            'status' => ContentGenerationStatus::Completed,
            'generated_content' => $variations,
        ]);

        $response = $this->getJson("/api/social_media_contents/generate/{$generationRequest->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'status' => 'completed',
                    'generated_content' => $variations,
                ],
            ]);
    }

    public function test_status_returns_failed_request_with_error(): void
    {
        $generationRequest = ContentGenerationRequest::create([
            'account_id' => $this->account->id,
            'type' => 'generate',
            'prompt' => 'Write a post',
            'platform' => 'twitter',
            'tone' => 'casual',
            'status' => ContentGenerationStatus::Failed,
            'error' => 'API rate limit exceeded.',
        ]);

        $response = $this->getJson("/api/social_media_contents/generate/{$generationRequest->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'status' => 'failed',
                    'error' => 'API rate limit exceeded.',
                ],
            ]);
    }

    public function test_status_forbidden_for_other_accounts_request(): void
    {
        $otherRequest = ContentGenerationRequest::create([
            'account_id' => $this->otherAccount->id,
            'type' => 'generate',
            'prompt' => 'Write a post',
            'platform' => 'twitter',
            'tone' => 'casual',
            'status' => ContentGenerationStatus::Pending,
        ]);

        $response = $this->getJson("/api/social_media_contents/generate/{$otherRequest->id}");

        $response->assertStatus(403);
    }

    public function test_status_returns_404_for_nonexistent_request(): void
    {
        $fakeUuid = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson("/api/social_media_contents/generate/{$fakeUuid}");

        $response->assertStatus(404);
    }
}
