<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GenerateVideoThumbnailJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

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
    public function handle(): void
    {
        $inputPath = tempnam(sys_get_temp_dir(), 'video_');
        $outputPath = tempnam(sys_get_temp_dir(), 'thumb_').'.jpg';

        try {
            file_put_contents($inputPath, Storage::disk('s3')->get($this->media->file_path));

            $result = Process::run([
                'ffmpeg', '-i', $inputPath,
                '-ss', '00:00:01.000',
                '-vframes', '1',
                '-y',
                $outputPath,
            ]);

            if (! $result->successful()) {
                throw new \RuntimeException('ffmpeg failed: '.$result->errorOutput());
            }

            $thumbnailKey = 'thumbnails/'.Str::uuid().'.jpg';
            Storage::disk('s3')->put($thumbnailKey, file_get_contents($outputPath));

            $this->media->update(['thumbnail_path' => $thumbnailKey]);
        } finally {
            @unlink($inputPath);
            @unlink($outputPath);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::warning('Failed to generate thumbnail for media #'.$this->media->id, [
            'error' => $exception?->getMessage(),
        ]);
    }
}
