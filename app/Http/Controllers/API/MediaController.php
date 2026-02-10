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
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Media', weight: 3)]
class MediaController extends Controller
{
    /**
     * List media for content.
     *
     * Returns all media attachments for the specified content item.
     * Supports filtering by type and file name, and sorting.
     */
    #[QueryParameter('filter[type]', description: 'Filter by media type: image, video.', type: 'string', example: 'image')]
    #[QueryParameter('filter[file_name]', description: 'Filter by partial file name match.', type: 'string', example: 'hero')]
    #[QueryParameter('sort', description: 'Sort by field. Prefix with - for descending. Allowed: created_at, updated_at.', type: 'string', example: '-created_at')]
    public function index(Request $request, SocialMediaContent $socialMediaContent): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Media::class, $socialMediaContent]);

        $media = QueryBuilder::for(
            $socialMediaContent->media()
        )
            ->allowedFilters([
                AllowedFilter::exact('type'),
                AllowedFilter::partial('file_name'),
            ])
            ->allowedSorts(['created_at', 'updated_at'])
            ->defaultSort('-created_at')
            ->get();

        return MediaResource::collection($media);
    }

    /**
     * Generate presigned upload URL.
     *
     * Returns a presigned PUT URL for direct video upload to S3/MinIO storage.
     * The client should use the returned URL and headers to upload the file directly.
     */
    #[Endpoint(operationId: 'getPresignedUrl')]
    #[Response(200, description: 'Presigned upload URL generated.', type: 'array{url: string, headers: array, key: string, expires_at: string}')]
    public function presignedUrl(PresignedUrlRequest $request, SocialMediaContent $socialMediaContent): JsonResponse
    {
        $this->authorize('create', [Media::class, $socialMediaContent]);

        $key = 'videos/'.Str::uuid().'_'.$request->validated('file_name');

        $expiresAt = now()->addMinutes(30);

        ['url' => $url, 'headers' => $headers] = Storage::disk('s3')->temporaryUploadUrl(
            $key,
            $expiresAt,
        );

        $url = Media::replaceMinioUrl($url);

        return response()->json([
            'url' => $url,
            'headers' => $headers,
            'key' => $key,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    /**
     * Get media.
     *
     * Returns a single media attachment by ID.
     */
    public function show(SocialMediaContent $socialMediaContent, Media $media): MediaResource
    {
        $this->authorize('view', [Media::class, $socialMediaContent, $media]);

        return new MediaResource($media);
    }

    /**
     * Attach media to content.
     *
     * Upload an image file directly or provide a storage key for a video already uploaded via presigned URL.
     * Images are limited to 2MB. Videos must be uploaded to S3 first using the presigned URL endpoint.
     *
     * @requestMediaType multipart/form-data
     */
    #[Endpoint(operationId: 'storeMedia')]
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
     * Delete media.
     *
     * Removes media from storage (S3 + Mux if video) and deletes the record.
     * Cleanup of S3 files and Mux assets is handled by the Media model's deleting event.
     */
    #[Endpoint(operationId: 'deleteMedia')]
    public function destroy(Request $request, SocialMediaContent $socialMediaContent, Media $media): JsonResponse
    {
        $this->authorize('delete', [Media::class, $socialMediaContent, $media]);

        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully.',
        ], 200);
    }
}
