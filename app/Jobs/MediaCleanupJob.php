<?php

namespace App\Jobs;

use App\Services\MuxService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MediaCleanupJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?string $filePath,
        public ?string $thumbnailPath,
        public ?string $muxAssetId,
    ) {}

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
    public function handle(MuxService $muxService): void
    {
        if ($this->filePath) {
            Storage::disk('s3')->delete($this->filePath);
        }

        if ($this->thumbnailPath) {
            Storage::disk('s3')->delete($this->thumbnailPath);
        }

        if ($this->muxAssetId) {
            $muxService->deleteAsset($this->muxAssetId);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::warning('Failed to clean up media files', [
            'file_path' => $this->filePath,
            'thumbnail_path' => $this->thumbnailPath,
            'mux_asset_id' => $this->muxAssetId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
