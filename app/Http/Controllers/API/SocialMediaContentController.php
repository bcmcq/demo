<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialMediaContentRequest;
use App\Http\Requests\UpdateSocialMediaContentRequest;
use App\Http\Resources\SocialMediaContentResource;
use App\Models\SocialMediaContent;
use App\QueryBuilders\HasRelationFilter;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Content', weight: 1)]
class SocialMediaContentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SocialMediaContent::class, 'social_media_content');
    }

    /**
     * List all content.
     *
     * Returns a paginated list of social media content for the authenticated user's account.
     * Supports filtering by category, relationship existence, includes, and sorting.
     */
    #[QueryParameter('filter[category]', description: 'Filter by category ID.', type: 'int', example: 1)]
    #[QueryParameter('filter[posts]', description: 'Filter by post existence: 1 = has posts, 0 = no posts.', type: 'string', example: '1')]
    #[QueryParameter('filter[schedules]', description: 'Filter by schedule existence: 1 = has schedules, 0 = no schedules.', type: 'string', example: '0')]
    #[QueryParameter('filter[media]', description: 'Filter by media existence: 1 = has media, 0 = no media.', type: 'string', example: '1')]
    #[QueryParameter('include', description: 'Comma-separated relationships to include: category, posts, schedules, media, rewrites.', type: 'string', example: 'category,media')]
    #[QueryParameter('sort', description: 'Sort by field. Prefix with - for descending. Allowed: title, created_at, updated_at.', type: 'string', example: '-created_at')]
    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 15, example: 25)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(
            SocialMediaContent::query()->forAccount($request->user()->account_id)
        )
            ->allowedFilters([
                AllowedFilter::exact('category', 'social_media_category_id'),
                AllowedFilter::custom('posts', new HasRelationFilter),
                AllowedFilter::custom('schedules', new HasRelationFilter),
                AllowedFilter::custom('media', new HasRelationFilter),
            ])
            ->allowedIncludes(['category', 'posts', 'schedules', 'media', 'rewrites'])
            ->allowedSorts(['title', 'created_at', 'updated_at'])
            ->defaultSort('-created_at')
            ->paginate($request->input('per_page', 15));

        return SocialMediaContentResource::collection($query);
    }

    /**
     * Get content.
     *
     * Returns a single content item with its category, posts, schedules, media, and rewrites.
     */
    public function show(SocialMediaContent $socialMediaContent): SocialMediaContentResource
    {
        $socialMediaContent->load(['category', 'posts', 'schedules', 'media', 'rewrites']);

        return new SocialMediaContentResource($socialMediaContent);
    }

    /**
     * Create content.
     *
     * Creates a new social media content item for the authenticated user's account.
     */
    public function store(StoreSocialMediaContentRequest $request): JsonResponse
    {
        $content = SocialMediaContent::query()->create([
            'account_id' => $request->user()->account_id,
            ...$request->validated(),
        ]);

        $content->load(['category', 'posts', 'schedules']);

        return (new SocialMediaContentResource($content))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update content.
     *
     * Updates an existing social media content item. Only the owner or an admin may update.
     */
    #[Endpoint(method: 'PUT')]
    public function update(UpdateSocialMediaContentRequest $request, SocialMediaContent $socialMediaContent): SocialMediaContentResource
    {
        $socialMediaContent->update($request->validated());

        $socialMediaContent->load(['category', 'posts', 'schedules']);

        return new SocialMediaContentResource($socialMediaContent);
    }

    /**
     * Delete content.
     *
     * Permanently removes a content item. Only the owner or an admin may delete.
     */
    #[Endpoint(operationId: 'deleteContent')]
    public function destroy(SocialMediaContent $socialMediaContent): JsonResponse
    {
        $socialMediaContent->delete();

        return response()->json([
            'message' => 'Content deleted successfully.',
        ], 200);
    }
}
