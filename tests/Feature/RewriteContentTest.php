<?php

namespace Tests\Feature;

use App\Enums\ContentGenerationStatus;
use App\Enums\Platform;
use App\Enums\Tone;
use App\Http\Middleware\BetterBeWillie;
use App\Jobs\GenerateContentJob;
use App\Models\Account;
use App\Models\ContentGenerationRequest;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\StructuredResponseFake;
use Prism\Prism\ValueObjects\Usage;
use Tests\TestCase;

class RewriteContentTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;

    private Account $otherAccount;

    private User $user;

    private User $otherUser;

    private SocialMediaCategory $category;

    private SocialMediaContent $content;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(BetterBeWillie::class);

        $this->account = Account::create([
            'name' => 'Test Account',
            'website' => 'https://test.com',
        ]);

        $this->otherAccount = Account::create([
            'name' => 'Other Account',
            'website' => 'https://other.com',
        ]);

        $this->user = User::forceCreate([
            'name' => 'Willie Dustice',
            'email' => 'willie@test.com',
            'password' => bcrypt('password'),
            'account_id' => $this->account->id,
            'is_admin' => false,
        ]);

        $this->otherUser = User::forceCreate([
            'name' => 'Other User',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
            'account_id' => $this->otherAccount->id,
            'is_admin' => false,
        ]);

        $this->actingAs($this->user);

        $this->category = SocialMediaCategory::create([
            'name' => 'holidays',
        ]);

        $this->content = SocialMediaContent::create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Happy Holidays',
            'content' => 'Wishing everyone a wonderful holiday season!',
        ]);
    }

    /** -------- HAPPY PATH -------- */
    public function test_rewrite_dispatches_job_and_returns_202(): void
    {
        Queue::fake();

        $response = $this->postJson("/api/social_media_contents/{$this->content->id}/rewrite", [
            'platform' => 'twitter',
            'tone' => 'casual',
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['message', 'generation_request_id']);

        Queue::assertPushed(GenerateContentJob::class, function (GenerateContentJob $job) {
            return $job->contentGenerationRequest->type === 'rewrite'
                && $job->contentGenerationRequest->platform === Platform::Twitter
                && $job->contentGenerationRequest->tone === Tone::Casual
                && $job->contentGenerationRequest->social_media_content_id === $this->content->id;
        });

        $this->assertDatabaseHas('content_generation_requests', [
            'account_id' => $this->account->id,
            'type' => 'rewrite',
            'platform' => 'twitter',
            'tone' => 'casual',
            'status' => ContentGenerationStatus::Pending->value,
            'social_media_content_id' => $this->content->id,
        ]);
    }

    public function test_rewrite_job_calls_prism_and_completes(): void
    {
        $variations = [
            'Happy holidays everyone! 🎄✨ #HolidaySeason',
            'Tis the season! Wishing you all joy and cheer 🎅 #Holidays',
            'Holiday vibes only! Hope yours is amazing 🎁 #HappyHolidays',
        ];

        $fakeResponse = StructuredResponseFake::make()
            ->withStructured(['variations' => $variations])
            ->withUsage(new Usage(50, 90))
            ->withFinishReason(FinishReason::Stop);

        Prism::fake([$fakeResponse]);

        $generationRequest = ContentGenerationRequest::create([
            'account_id' => $this->account->id,
            'type' => 'rewrite',
            'prompt' => "Rewrite: {$this->content->title}",
            'platform' => 'twitter',
            'tone' => 'casual',
            'status' => ContentGenerationStatus::Pending,
            'social_media_content_id' => $this->content->id,
        ]);

        $job = new GenerateContentJob($generationRequest);
        $job->handle(app(\App\Services\AIContentWriterService::class));

        $generationRequest->refresh();

        $this->assertEquals(ContentGenerationStatus::Completed, $generationRequest->status);
        $this->assertIsArray($generationRequest->generated_content);
        $this->assertCount(3, $generationRequest->generated_content);
        $this->assertEquals($variations, $generationRequest->generated_content);
    }

    public function test_rewrite_with_all_valid_platforms(): void
    {
        Queue::fake();

        foreach (['twitter', 'instagram', 'facebook', 'linkedin'] as $platform) {
            $response = $this->postJson("/api/social_media_contents/{$this->content->id}/rewrite", [
                'platform' => $platform,
                'tone' => 'professional',
            ]);

            $response->assertStatus(202);
        }

        Queue::assertPushed(GenerateContentJob::class, 4);
    }

    public function test_rewrite_with_all_valid_tones(): void
    {
        Queue::fake();

        foreach (['professional', 'casual', 'humorous', 'formal'] as $tone) {
            $response = $this->postJson("/api/social_media_contents/{$this->content->id}/rewrite", [
                'platform' => 'twitter',
                'tone' => $tone,
            ]);

            $response->assertStatus(202);
        }

        Queue::assertPushed(GenerateContentJob::class, 4);
    }

    /** -------- VALIDATION -------- */
    public function test_rewrite_requires_platform(): void
    {
        $response = $this->postJson("/api/social_media_contents/{$this->content->id}/rewrite", [
            'tone' => 'casual',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['platform']);
    }

    public function test_rewrite_requires_tone(): void
    {
        $response = $this->postJson("/api/social_media_contents/{$this->content->id}/rewrite", [
            'platform' => 'twitter',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tone']);
    }

    public function test_rewrite_rejects_invalid_platform(): void
    {
        $response = $this->postJson("/api/social_media_contents/{$this->content->id}/rewrite", [
            'platform' => 'tiktok',
            'tone' => 'casual',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['platform']);
    }

    public function test_rewrite_rejects_invalid_tone(): void
    {
        $response = $this->postJson("/api/social_media_contents/{$this->content->id}/rewrite", [
            'platform' => 'twitter',
            'tone' => 'angry',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tone']);
    }

    /** -------- AUTHORIZATION -------- */
    public function test_rewrite_forbidden_for_other_accounts_content(): void
    {
        $otherContent = SocialMediaContent::create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $this->category->id,
            'title' => 'Other Content',
            'content' => 'Not yours.',
        ]);

        $response = $this->postJson("/api/social_media_contents/{$otherContent->id}/rewrite", [
            'platform' => 'twitter',
            'tone' => 'casual',
        ]);

        $response->assertStatus(403);
    }

    public function test_rewrite_returns_404_for_nonexistent_content(): void
    {
        $response = $this->postJson('/api/social_media_contents/99999/rewrite', [
            'platform' => 'twitter',
            'tone' => 'casual',
        ]);

        $response->assertStatus(404);
    }

    /** -------- JOB FAILURE -------- */
    public function test_rewrite_job_handles_failure_gracefully(): void
    {
        $generationRequest = ContentGenerationRequest::create([
            'account_id' => $this->account->id,
            'type' => 'rewrite',
            'prompt' => "Rewrite: {$this->content->title}",
            'platform' => 'twitter',
            'tone' => 'casual',
            'status' => ContentGenerationStatus::Pending,
            'social_media_content_id' => $this->content->id,
        ]);

        $job = new GenerateContentJob($generationRequest);
        $job->failed(new \RuntimeException('API rate limit exceeded'));

        $generationRequest->refresh();

        $this->assertEquals(ContentGenerationStatus::Failed, $generationRequest->status);
        $this->assertEquals('API rate limit exceeded', $generationRequest->error);
    }
}
