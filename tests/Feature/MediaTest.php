<?php

namespace Tests\Feature;

use App\Jobs\GenerateVideoThumbnailJob;
use App\Jobs\MediaCleanupJob;
use App\Jobs\UploadToMuxJob;
use App\Models\Account;
use App\Models\Media;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use App\Models\User;
use App\Services\MuxService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

class MediaTest extends BaseTestCase
{
    private Account $otherAccount;

    private User $otherUser;

    private SocialMediaContent $content;

    private SocialMediaContent $otherContent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->otherAccount = $this->createOtherAccount();
        $this->otherUser = $this->createUserForAccount($this->otherAccount);

        $category = SocialMediaCategory::factory()->create(['name' => 'holidays']);

        $this->content = SocialMediaContent::factory()->create([
            'account_id' => $this->account->id,
            'social_media_category_id' => $category->id,
            'title' => 'Test Content',
            'content' => 'Test body text.',
        ]);

        $this->otherContent = SocialMediaContent::factory()->create([
            'account_id' => $this->otherAccount->id,
            'social_media_category_id' => $category->id,
            'title' => 'Other Content',
            'content' => 'Other body text.',
        ]);
    }

    /** -------- IMAGE UPLOAD -------- */
    public function test_upload_image_successfully(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('photo.jpg', 640, 480)->size(500);

        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media",
            ['file' => $file]
        );

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'image')
            ->assertJsonPath('data.file_name', 'photo.jpg')
            ->assertJsonPath('data.social_media_content_id', $this->content->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'social_media_content_id',
                    'type',
                    'file_name',
                    'mime_type',
                    'size',
                    'url',
                    'thumbnail_url',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('media', [
            'social_media_content_id' => $this->content->id,
            'type' => 'image',
            'file_name' => 'photo.jpg',
        ]);

        $media = Media::query()->first();
        $this->assertStringStartsWith('images/', $media->file_path);
        Storage::disk('s3')->assertExists($media->file_path);
    }

    public function test_upload_image_too_large_returns_422(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('large.jpg')->size(3000);

        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media",
            ['file' => $file]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_upload_image_invalid_type_returns_422(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media",
            ['file' => $file]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_upload_image_missing_both_file_and_storage_key_returns_422(): void
    {
        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media",
            []
        );

        $response->assertStatus(422);
    }

    public function test_upload_webp_image_successfully(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('banner.webp', 800, 600)->size(1000);

        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media",
            ['file' => $file]
        );

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'image')
            ->assertJsonPath('data.file_name', 'banner.webp');
    }

    public function test_upload_png_image_successfully(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('icon.png', 256, 256)->size(200);

        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media",
            ['file' => $file]
        );

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'image')
            ->assertJsonPath('data.file_name', 'icon.png');
    }

    public function test_image_at_exactly_2mb_limit_passes(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('exactly2mb.jpg', 640, 480)->size(2048);

        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media",
            ['file' => $file]
        );

        $response->assertStatus(201);
    }

    public function test_image_just_over_2mb_limit_fails(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('over2mb.jpg', 640, 480)->size(2049);

        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media",
            ['file' => $file]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /** -------- PRESIGNED URL -------- */
    public function test_presigned_url_returns_url_and_metadata(): void
    {
        $mockDisk = \Mockery::mock(\Illuminate\Contracts\Filesystem\Filesystem::class);
        $mockDisk->shouldReceive('temporaryUploadUrl')
            ->once()
            ->andReturn([
                'url' => 'https://minio:9000/local/videos/test-upload.mp4?presigned=1',
                'headers' => ['Content-Type' => 'application/octet-stream'],
            ]);

        Storage::shouldReceive('disk')
            ->with('s3')
            ->andReturn($mockDisk);

        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media/presigned_url",
            ['file_name' => 'intro.mp4', 'content_type' => 'video/mp4']
        );

        $response->assertOk()
            ->assertJsonStructure([
                'url',
                'headers',
                'key',
                'expires_at',
            ]);

        $this->assertStringStartsWith('videos/', $response->json('key'));
    }

    public function test_presigned_url_missing_file_name_returns_422(): void
    {
        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media/presigned_url",
            []
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_name']);
    }

    public function test_presigned_url_invalid_content_type_returns_422(): void
    {
        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media/presigned_url",
            [
                'file_name' => 'intro.mp4',
                'content_type' => 'image/jpeg',
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content_type']);
    }

    public function test_presigned_url_forbidden_for_other_accounts_content(): void
    {
        $response = $this->postJson(
            "/api/social_media_contents/{$this->otherContent->id}/media/presigned_url",
            ['file_name' => 'intro.mp4']
        );

        $response->assertForbidden();
    }

    /** -------- VIDEO STORE -------- */
    public function test_store_video_with_storage_key_dispatches_jobs(): void
    {
        Storage::fake('s3');
        Bus::fake([GenerateVideoThumbnailJob::class, UploadToMuxJob::class]);

        $storageKey = 'videos/test-video.mp4';
        Storage::disk('s3')->put($storageKey, 'fake video content');

        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media",
            [
                'storage_key' => $storageKey,
                'file_name' => 'intro.mp4',
            ]
        );

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'video')
            ->assertJsonPath('data.file_name', 'intro.mp4');

        $this->assertDatabaseHas('media', [
            'social_media_content_id' => $this->content->id,
            'type' => 'video',
            'file_name' => 'intro.mp4',
            'file_path' => $storageKey,
        ]);

        Bus::assertDispatched(GenerateVideoThumbnailJob::class);
        Bus::assertDispatched(UploadToMuxJob::class);
    }

    public function test_store_video_with_nonexistent_storage_key_returns_422(): void
    {
        Storage::fake('s3');

        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media",
            [
                'storage_key' => 'videos/does-not-exist.mp4',
                'file_name' => 'intro.mp4',
            ]
        );

        $response->assertStatus(422)
            ->assertJsonPath('errors.storage_key.0', 'The file was not found in storage.');
    }

    public function test_store_video_missing_file_name_with_storage_key_returns_422(): void
    {
        Storage::fake('s3');

        $storageKey = 'videos/test-video.mp4';
        Storage::disk('s3')->put($storageKey, 'fake video content');

        $response = $this->postJson(
            "/api/social_media_contents/{$this->content->id}/media",
            ['storage_key' => $storageKey]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_name']);
    }

    /** -------- LIST MEDIA -------- */
    public function test_list_media_returns_images_and_videos(): void
    {
        Media::factory()->create([
            'social_media_content_id' => $this->content->id,
            'file_name' => 'photo.jpg',
        ]);

        Media::factory()->video()->create([
            'social_media_content_id' => $this->content->id,
            'file_name' => 'intro.mp4',
        ]);

        $response = $this->getJson(
            "/api/social_media_contents/{$this->content->id}/media"
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_media_with_no_media_returns_empty_collection(): void
    {
        $response = $this->getJson(
            "/api/social_media_contents/{$this->content->id}/media"
        );

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    /** -------- SHOW MEDIA -------- */
    public function test_show_media_returns_single_media_item(): void
    {
        $media = Media::factory()->create([
            'social_media_content_id' => $this->content->id,
            'file_name' => 'photo.jpg',
        ]);

        $response = $this->getJson(
            "/api/social_media_contents/{$this->content->id}/media/{$media->id}"
        );

        $response->assertOk()
            ->assertJsonPath('data.id', $media->id)
            ->assertJsonPath('data.file_name', 'photo.jpg')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'social_media_content_id',
                    'type',
                    'file_name',
                    'mime_type',
                    'size',
                    'url',
                    'thumbnail_url',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_show_video_media_returns_playback_url(): void
    {
        $media = Media::factory()->video()->create([
            'social_media_content_id' => $this->content->id,
            'file_name' => 'intro.mp4',
            'mux_playback_id' => 'playback_123',
        ]);

        $response = $this->getJson(
            "/api/social_media_contents/{$this->content->id}/media/{$media->id}"
        );

        $response->assertOk()
            ->assertJsonPath('data.id', $media->id)
            ->assertJsonPath('data.type', 'video');
    }

    public function test_show_media_forbidden_for_other_accounts_content(): void
    {
        $media = Media::factory()->create([
            'social_media_content_id' => $this->otherContent->id,
        ]);

        $response = $this->getJson(
            "/api/social_media_contents/{$this->otherContent->id}/media/{$media->id}"
        );

        $response->assertForbidden();
    }

    public function test_show_media_returns_404_for_nonexistent_media(): void
    {
        $response = $this->getJson(
            "/api/social_media_contents/{$this->content->id}/media/99999"
        );

        $response->assertNotFound();
    }

    /** -------- DELETE IMAGE -------- */
    public function test_delete_image_dispatches_cleanup_job(): void
    {
        Bus::fake([MediaCleanupJob::class]);

        $media = Media::factory()->create([
            'social_media_content_id' => $this->content->id,
            'file_path' => 'images/test-image.jpg',
        ]);

        $response = $this->deleteJson(
            "/api/social_media_contents/{$this->content->id}/media/{$media->id}"
        );

        $response->assertOk()
            ->assertJsonPath('message', 'Media deleted successfully.');

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Bus::assertDispatched(MediaCleanupJob::class);
    }

    /** -------- DELETE VIDEO -------- */
    public function test_delete_video_dispatches_cleanup_job_with_mux(): void
    {
        Bus::fake([MediaCleanupJob::class]);

        $media = Media::factory()->video()->create([
            'social_media_content_id' => $this->content->id,
            'file_path' => 'videos/test-video.mp4',
            'thumbnail_path' => 'thumbnails/test-thumb.jpg',
            'mux_asset_id' => 'asset_to_delete',
        ]);

        $response = $this->deleteJson(
            "/api/social_media_contents/{$this->content->id}/media/{$media->id}"
        );

        $response->assertOk()
            ->assertJsonPath('message', 'Media deleted successfully.');

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Bus::assertDispatched(MediaCleanupJob::class, function (MediaCleanupJob $job) {
            return $job->filePath === 'videos/test-video.mp4'
                && $job->thumbnailPath === 'thumbnails/test-thumb.jpg'
                && $job->muxAssetId === 'asset_to_delete';
        });
    }

    /** -------- AUTHORIZATION -------- */
    public function test_cannot_upload_image_to_another_accounts_content(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('photo.jpg', 640, 480)->size(500);

        $response = $this->postJson(
            "/api/social_media_contents/{$this->otherContent->id}/media",
            ['file' => $file]
        );

        $response->assertForbidden();
    }

    public function test_cannot_store_video_for_another_accounts_content(): void
    {
        Storage::fake('s3');

        $response = $this->postJson(
            "/api/social_media_contents/{$this->otherContent->id}/media",
            ['storage_key' => 'videos/test.mp4', 'file_name' => 'intro.mp4']
        );

        $response->assertForbidden();
    }

    public function test_cannot_list_media_for_another_accounts_content(): void
    {
        $response = $this->getJson(
            "/api/social_media_contents/{$this->otherContent->id}/media"
        );

        $response->assertForbidden();
    }

    public function test_cannot_delete_another_accounts_media(): void
    {
        $media = Media::factory()->create([
            'social_media_content_id' => $this->otherContent->id,
        ]);

        $response = $this->deleteJson(
            "/api/social_media_contents/{$this->otherContent->id}/media/{$media->id}"
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    /** -------- EDGE CASES -------- */
    public function test_delete_returns_404_for_nonexistent_media(): void
    {
        $response = $this->deleteJson(
            "/api/social_media_contents/{$this->content->id}/media/9999"
        );

        $response->assertNotFound();
    }

    /** -------- THUMBNAIL JOB -------- */
    public function test_generate_video_thumbnail_job_creates_thumbnail(): void
    {
        Storage::fake('s3');

        Process::fake(function (\Illuminate\Process\PendingProcess $process) {
            $command = $process->command;
            if (is_array($command)) {
                $outputFile = end($command);
                if ($outputFile && str_ends_with($outputFile, '.jpg')) {
                    file_put_contents($outputFile, 'fake thumbnail jpeg data');
                }
            }

            return Process::result(output: '', errorOutput: '', exitCode: 0);
        });

        $videoPath = 'videos/test-video.mp4';
        Storage::disk('s3')->put($videoPath, 'fake video content');

        $media = Media::factory()->video()->create([
            'social_media_content_id' => $this->content->id,
            'file_path' => $videoPath,
            'thumbnail_path' => null,
        ]);

        $job = new GenerateVideoThumbnailJob($media);
        $job->handle();

        $media->refresh();
        $this->assertNotNull($media->thumbnail_path);
        $this->assertStringStartsWith('thumbnails/', $media->thumbnail_path);
        Storage::disk('s3')->assertExists($media->thumbnail_path);
    }

    /** -------- MUX UPLOAD JOB -------- */
    public function test_upload_to_mux_job_uploads_file_content_directly(): void
    {
        Storage::fake('s3');

        $videoPath = 'videos/test-video.mp4';
        Storage::disk('s3')->put($videoPath, 'fake video content');

        $media = Media::factory()->video()->create([
            'social_media_content_id' => $this->content->id,
            'file_path' => $videoPath,
        ]);

        $muxService = $this->mock(MuxService::class, function (MockInterface $mock) {
            $mock->shouldReceive('uploadFileContent')
                ->once()
                ->with('fake video content')
                ->andReturn([
                    'asset_id' => 'asset_abc',
                    'playback_id' => 'playback_xyz',
                ]);
        });

        $job = new UploadToMuxJob($media);
        $job->handle($muxService);

        $media->refresh();
        $this->assertEquals('asset_abc', $media->mux_asset_id);
        $this->assertEquals('playback_xyz', $media->mux_playback_id);
    }

    public function test_upload_to_mux_job_logs_warning_on_failure(): void
    {
        $media = Media::factory()->video()->create([
            'social_media_content_id' => $this->content->id,
        ]);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function (string $message, array $context) use ($media) {
                return str_contains($message, (string) $media->id)
                    && $context['error'] === 'Mux API error';
            });

        $job = new UploadToMuxJob($media);
        $job->failed(new \RuntimeException('Mux API error'));
    }
}
