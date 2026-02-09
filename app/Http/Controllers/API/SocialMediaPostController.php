<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialMediaPostRequest;
use App\Http\Resources\SocialMediaPostResource;
use App\Models\SocialMediaPost;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Posts', weight: 2)]
class SocialMediaPostController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SocialMediaPost::class, 'social_media_post');
    }

    /**
     * List all posts.
     *
     * Returns a paginated list of posts for the authenticated user's account.
     */
    #[QueryParameter('filter[content]', description: 'Filter by content ID.', type: 'int', example: 1)]
    #[QueryParameter('include', description: 'Comma-separated relationships to include: content.', type: 'string', example: 'content')]
    #[QueryParameter('sort', description: 'Sort by field. Prefix with - for descending. Allowed: posted_at.', type: 'string', example: '-posted_at')]
    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 15, example: 25)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(
            SocialMediaPost::query()->where('account_id', $request->user()->account_id)
        )
            ->allowedFilters([
                AllowedFilter::exact('content', 'social_media_content_id'),
            ])
            ->allowedIncludes(['content'])
            ->allowedSorts(['posted_at'])
            ->defaultSort('-posted_at')
            ->paginate($request->input('per_page', 15));

        return SocialMediaPostResource::collection($query);
    }

    /**
     * Get a post.
     *
     * Returns a single post by ID with its associated content.
     */
    public function show(SocialMediaPost $socialMediaPost): SocialMediaPostResource
    {
        $socialMediaPost->load('content');

        return new SocialMediaPostResource($socialMediaPost);
    }

    /**
     * Create a post.
     *
     * Records a new post for the authenticated user's account.
     */
    public function store(StoreSocialMediaPostRequest $request): JsonResponse
    {
        $post = SocialMediaPost::query()->create([
            'account_id' => $request->user()->account_id,
            'social_media_content_id' => $request->validated('social_media_content_id'),
            'posted_at' => $request->validated('posted_at') ?? now(),
        ]);

        $post->load('content');

        return (new SocialMediaPostResource($post))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Delete a post.
     *
     * Permanently removes a post record. Only the owner or an admin may delete.
     */
    #[Endpoint(operationId: 'deletePost')]
    public function destroy(SocialMediaPost $socialMediaPost): JsonResponse
    {
        $socialMediaPost->delete();

        return response()->json([
            'message' => 'Post deleted successfully.',
        ], 200);
    }
}
