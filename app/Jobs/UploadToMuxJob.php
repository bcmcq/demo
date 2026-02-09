<?php

namespace App\Jobs;

use App\Models\Media;
use App\Services\MuxService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UploadToMuxJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(public Media $media) {}

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
        $temporaryUrl = Storage::disk('s3')->temporaryUrl($this->media->file_path, now()->addMinutes(30));

        $assetData = $muxService->createAssetFromUrl($temporaryUrl);

        $this->media->update([
            'mux_asset_id' => $assetData['asset_id'],
            'mux_playback_id' => $assetData['playback_id'],
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::warning('Failed to upload to Mux for media #'.$this->media->id, [
            'error' => $exception?->getMessage(),
        ]);
    }
}
