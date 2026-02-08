<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialMediaPostRequest;
use App\Http\Resources\SocialMediaPostResource;
use App\Models\SocialMediaPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SocialMediaPostController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SocialMediaPost::class, 'social_media_post');
    }

    /**
     * Display a paginated listing of posts for the authenticated user's account.
     */
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
     * Display the specified post.
     */
    public function show(SocialMediaPost $socialMediaPost): SocialMediaPostResource
    {
        $socialMediaPost->load('content');

        return new SocialMediaPostResource($socialMediaPost);
    }

    /**
     * Store a newly created post for the authenticated user's account.
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
     * Remove the specified post.
     */
    public function destroy(SocialMediaPost $socialMediaPost): JsonResponse
    {
        $socialMediaPost->delete();

        return response()->json([
            'message' => 'Post deleted successfully.',
        ], 200);
    }
}
