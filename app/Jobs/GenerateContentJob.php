<?php

namespace App\Jobs;

use App\Enums\ContentGenerationStatus;
use App\Models\ContentGenerationRequest;
use App\Services\AIContentWriterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateContentJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public ContentGenerationRequest $contentGenerationRequest) {}

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 30, 120];
    }

    /**
     * Execute the job.
     */
    public function handle(AIContentWriterService $service): void
    {
        $this->contentGenerationRequest->update([
            'status' => ContentGenerationStatus::Processing,
        ]);

        $generatedContent = match ($this->contentGenerationRequest->type) {
            'rewrite' => $service->rewrite(
                $this->contentGenerationRequest->socialMediaContent,
                $this->contentGenerationRequest->platform,
                $this->contentGenerationRequest->tone,
            ),
            'generate' => $service->generate(
                $this->contentGenerationRequest->prompt,
                $this->contentGenerationRequest->platform,
                $this->contentGenerationRequest->tone,
            ),
        };

        $this->contentGenerationRequest->update([
            'status' => ContentGenerationStatus::Completed,
            'generated_content' => $generatedContent,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $this->contentGenerationRequest->update([
            'status' => ContentGenerationStatus::Failed,
            'error' => $exception?->getMessage() ?? 'An unknown error occurred.',
        ]);
    }
}
