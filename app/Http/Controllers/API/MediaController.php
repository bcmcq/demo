<?php

namespace App\Http\Controllers\API;

use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Http\Requests\PresignedUrlRequest;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Resources\MediaResource;
use App\Jobs\GenerateVideoThumbnailJob;
use App\Jobs\UploadToMuxJob;
use App\Models\Media;
use App\Models\SocialMediaContent;
use App\Services\MuxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Display a listing of media for the given content.
     */
    public function index(SocialMediaContent $socialMediaContent): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Media::class, $socialMediaContent]);

        $media = $socialMediaContent->media()->latest()->get();

        return MediaResource::collection($media);
    }

    /**
     * Generate a presigned PUT URL for direct video upload to S3.
     */
    public function presignedUrl(PresignedUrlRequest $request, SocialMediaContent $socialMediaContent): JsonResponse
    {
        $this->authorize('create', [Media::class, $socialMediaContent]);

        $key = 'videos/'.Str::uuid().'_'.$request->validated('file_name');

        $expiresAt = now()->addMinutes(30);

        ['url' => $url, 'headers' => $headers] = Storage::disk('s3')->temporaryUploadUrl(
            $key,
            $expiresAt,
        );

        $forwardedPort = config('services.minio.forwarded_port', 9000);
        $url = str_replace('http://minio:9000', "http://localhost:{$forwardedPort}", $url);

        return response()->json([
            'url' => $url,
            'headers' => $headers,
            'key' => $key,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    /**
     * Display the specified media.
     */
    public function show(SocialMediaContent $socialMediaContent, Media $media): MediaResource
    {
        $this->authorize('view', [Media::class, $socialMediaContent, $media]);

        return new MediaResource($media);
    }

    /**
     * Store a new media file for the given content.
     *
     * Accepts either an image file upload or a storage key for a video already uploaded to S3.
     */
    public function store(StoreMediaRequest $request, SocialMediaContent $socialMediaContent): JsonResponse
    {
        $this->authorize('create', [Media::class, $socialMediaContent]);

        if ($request->hasFile('file')) {
            return $this->storeImage($request, $socialMediaContent);
        }

        return $this->storeVideo($request, $socialMediaContent);
    }

    /**
     * Handle image upload path: store file on S3 and create Media record.
     */
    private function storeImage(StoreMediaRequest $request, SocialMediaContent $socialMediaContent): JsonResponse
    {
        $file = $request->file('file');
        $path = Storage::disk('s3')->putFileAs(
            'images',
            $file,
            Str::uuid().'_'.$file->getClientOriginalName()
        );

        $media = $socialMediaContent->media()->create([
            'type' => MediaType::Image,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return (new MediaResource($media))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Handle video path: validate S3 key exists, create Media record, dispatch jobs.
     */
    private function storeVideo(StoreMediaRequest $request, SocialMediaContent $socialMediaContent): JsonResponse
    {
        $storageKey = $request->validated('storage_key');

        $fileSize = rescue(fn () => Storage::disk('s3')->size($storageKey));

        if ($fileSize === null) {
            return response()->json([
                'message' => 'The specified storage key does not exist.',
                'errors' => ['storage_key' => ['The file was not found in storage.']],
            ], 422);
        }

        $media = $socialMediaContent->media()->create([
            'type' => MediaType::Video,
            'file_path' => $storageKey,
            'file_name' => $request->validated('file_name'),
            'mime_type' => 'video/mp4',
            'size' => $fileSize,
        ]);

        GenerateVideoThumbnailJob::dispatch($media);
        UploadToMuxJob::dispatch($media);

        return (new MediaResource($media))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Remove the specified media from storage.
     */
    public function destroy(Request $request, SocialMediaContent $socialMediaContent, Media $media, MuxService $muxService): JsonResponse
    {
        $this->authorize('delete', [Media::class, $socialMediaContent, $media]);

        if ($media->file_path) {
            Storage::disk('s3')->delete($media->file_path);
        }

        if ($media->thumbnail_path) {
            Storage::disk('s3')->delete($media->thumbnail_path);
        }

        if ($media->isVideo() && $media->mux_asset_id) {
            $muxService->deleteAsset($media->mux_asset_id);
        }

        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully.',
        ], 200);
    }
}
