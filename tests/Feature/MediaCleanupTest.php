<?php

namespace Tests\Feature;

use App\Jobs\MediaCleanupJob;
use App\Models\Media;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use App\Services\MuxService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

class MediaCleanupTest extends BaseTestCase
{
    private SocialMediaContent $content;

    protected function setUp(): void
    {
        parent::setUp();

        $category = SocialMediaCategory::factory()->create(['name' => 'cleanup-test']);

        $this->content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $category->id,
        ]);
    }

    /** -------- SOCIAL MEDIA CONTENT DELETING EVENT -------- */
    public function test_deleting_content_deletes_associated_media(): void
    {
        Bus::fake([MediaCleanupJob::class]);

        $imageMedia = Media::factory()->create([
            'social_media_content_id' => $this->content->id,
        ]);
        $videoMedia = Media::factory()->video()->create([
            'social_media_content_id' => $this->content->id,
        ]);

        $this->content->delete();

        $this->assertDatabaseMissing('media', ['id' => $imageMedia->id]);
        $this->assertDatabaseMissing('media', ['id' => $videoMedia->id]);
        $this->assertDatabaseMissing('social_media_contents', ['id' => $this->content->id]);
    }

    public function test_deleting_content_dispatches_cleanup_job_for_each_media(): void
    {
        Bus::fake([MediaCleanupJob::class]);

        Media::factory()->create([
            'social_media_content_id' => $this->content->id,
            'file_path' => 'images/photo1.jpg',
        ]);
        Media::factory()->video()->create([
            'social_media_content_id' => $this->content->id,
            'file_path' => 'videos/intro.mp4',
            'mux_asset_id' => 'asset_abc',
        ]);

        $this->content->delete();

        Bus::assertDispatched(MediaCleanupJob::class, 2);
    }

    public function test_deleting_content_with_no_media_succeeds(): void
    {
        Bus::fake([MediaCleanupJob::class]);

        $this->content->delete();

        $this->assertDatabaseMissing('social_media_contents', ['id' => $this->content->id]);
        Bus::assertNotDispatched(MediaCleanupJob::class);
    }

    /** -------- MEDIA DELETING EVENT -------- */
    public function test_deleting_media_dispatches_cleanup_job(): void
    {
        Bus::fake([MediaCleanupJob::class]);

        $media = Media::factory()->create([
            'social_media_content_id' => $this->content->id,
            'file_path' => 'images/test.jpg',
            'thumbnail_path' => null,
        ]);

        $media->delete();

        Bus::assertDispatched(MediaCleanupJob::class, function (MediaCleanupJob $job) {
            return $job->filePath === 'images/test.jpg'
                && $job->thumbnailPath === null
                && $job->muxAssetId === null;
        });
    }

    public function test_deleting_video_media_dispatches_cleanup_job_with_mux_asset_id(): void
    {
        Bus::fake([MediaCleanupJob::class]);

        $media = Media::factory()->video()->create([
            'social_media_content_id' => $this->content->id,
            'file_path' => 'videos/intro.mp4',
            'thumbnail_path' => 'thumbnails/thumb.jpg',
            'mux_asset_id' => 'asset_xyz',
        ]);

        $media->delete();

        Bus::assertDispatched(MediaCleanupJob::class, function (MediaCleanupJob $job) {
            return $job->filePath === 'videos/intro.mp4'
                && $job->thumbnailPath === 'thumbnails/thumb.jpg'
                && $job->muxAssetId === 'asset_xyz';
        });
    }

    public function test_deleting_video_without_mux_asset_dispatches_job_with_null_mux_id(): void
    {
        Bus::fake([MediaCleanupJob::class]);

        $media = Media::factory()->video()->create([
            'social_media_content_id' => $this->content->id,
            'mux_asset_id' => null,
        ]);

        $media->delete();

        Bus::assertDispatched(MediaCleanupJob::class, function (MediaCleanupJob $job) {
            return $job->muxAssetId === null;
        });
    }

    /** -------- MEDIA CLEANUP JOB EXECUTION -------- */
    public function test_cleanup_job_deletes_image_files_from_s3(): void
    {
        Storage::fake('s3');

        $filePath = 'images/test-image.jpg';
        Storage::disk('s3')->put($filePath, 'fake image data');
        Storage::disk('s3')->assertExists($filePath);

        $job = new MediaCleanupJob(
            filePath: $filePath,
            thumbnailPath: null,
            muxAssetId: null,
        );

        $muxService = $this->mock(MuxService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('deleteAsset');
        });

        $job->handle($muxService);

        Storage::disk('s3')->assertMissing($filePath);
    }

    public function test_cleanup_job_deletes_video_files_and_mux_asset(): void
    {
        Storage::fake('s3');

        $videoPath = 'videos/test-video.mp4';
        $thumbnailPath = 'thumbnails/test-thumb.jpg';
        Storage::disk('s3')->put($videoPath, 'fake video');
        Storage::disk('s3')->put($thumbnailPath, 'fake thumb');

        $muxService = $this->mock(MuxService::class, function (MockInterface $mock) {
            $mock->shouldReceive('deleteAsset')
                ->once()
                ->with('asset_to_delete');
        });

        $job = new MediaCleanupJob(
            filePath: $videoPath,
            thumbnailPath: $thumbnailPath,
            muxAssetId: 'asset_to_delete',
        );

        $job->handle($muxService);

        Storage::disk('s3')->assertMissing($videoPath);
        Storage::disk('s3')->assertMissing($thumbnailPath);
    }

    public function test_cleanup_job_handles_all_null_paths_gracefully(): void
    {
        Storage::fake('s3');

        $muxService = $this->mock(MuxService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('deleteAsset');
        });

        $job = new MediaCleanupJob(
            filePath: null,
            thumbnailPath: null,
            muxAssetId: null,
        );

        $job->handle($muxService);

        $this->assertTrue(true);
    }

    public function test_cleanup_job_logs_warning_on_failure(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return $message === 'Failed to clean up media files'
                    && $context['file_path'] === 'images/orphan.jpg'
                    && $context['error'] === 'S3 unreachable';
            });

        $job = new MediaCleanupJob(
            filePath: 'images/orphan.jpg',
            thumbnailPath: null,
            muxAssetId: null,
        );

        $job->failed(new \RuntimeException('S3 unreachable'));
    }

    /** -------- CONTROLLER INTEGRATION -------- */
    public function test_controller_destroy_dispatches_cleanup_job(): void
    {
        Bus::fake([MediaCleanupJob::class]);

        $media = Media::factory()->create([
            'social_media_content_id' => $this->content->id,
            'file_path' => 'images/controller-test.jpg',
        ]);

        $response = $this->deleteJson(
            "/api/social_media_contents/{$this->content->id}/media/{$media->id}"
        );

        $response->assertOk()
            ->assertJsonPath('message', 'Media deleted successfully.');

        $this->assertDatabaseMissing('media', ['id' => $media->id]);

        Bus::assertDispatched(MediaCleanupJob::class, function (MediaCleanupJob $job) {
            return $job->filePath === 'images/controller-test.jpg';
        });
    }

    public function test_controller_destroy_video_dispatches_cleanup_with_mux(): void
    {
        Bus::fake([MediaCleanupJob::class]);

        $media = Media::factory()->video()->create([
            'social_media_content_id' => $this->content->id,
            'file_path' => 'videos/controller-test.mp4',
            'thumbnail_path' => 'thumbnails/controller-thumb.jpg',
            'mux_asset_id' => 'asset_controller',
        ]);

        $response = $this->deleteJson(
            "/api/social_media_contents/{$this->content->id}/media/{$media->id}"
        );

        $response->assertOk();

        Bus::assertDispatched(MediaCleanupJob::class, function (MediaCleanupJob $job) {
            return $job->filePath === 'videos/controller-test.mp4'
                && $job->thumbnailPath === 'thumbnails/controller-thumb.jpg'
                && $job->muxAssetId === 'asset_controller';
        });
    }
}
