<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialMediaContentRequest;
use App\Http\Requests\UpdateSocialMediaContentRequest;
use App\Http\Resources\SocialMediaContentResource;
use App\Models\SocialMediaContent;
use App\QueryBuilders\HasRelationFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SocialMediaContentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SocialMediaContent::class, 'social_media_content');
    }

    /**
     * Display a paginated listing of social media content for the authenticated user's account.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(
            SocialMediaContent::query()->forAccount($request->user()->account_id)
        )
            ->allowedFilters([
                AllowedFilter::exact('category', 'social_media_category_id'),
                AllowedFilter::custom('posts', new HasRelationFilter),
                AllowedFilter::custom('schedules', new HasRelationFilter),
            ])
            ->allowedIncludes(['category', 'posts', 'schedules', 'media'])
            ->allowedSorts(['title', 'created_at', 'updated_at'])
            ->defaultSort('-created_at')
            // ->with(['category', 'posts', 'schedules', 'media'])
            ->paginate($request->input('per_page', 15));

        return SocialMediaContentResource::collection($query);
    }

    /**
     * Display the specified social media content.
     */
    public function show(SocialMediaContent $socialMediaContent): SocialMediaContentResource
    {
        $socialMediaContent->load(['category', 'posts', 'schedules', 'media']);

        return new SocialMediaContentResource($socialMediaContent);
    }

    /**
     * Store a newly created social media content for the authenticated user's account.
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
     * Update the specified social media content.
     */
    public function update(UpdateSocialMediaContentRequest $request, SocialMediaContent $socialMediaContent): SocialMediaContentResource
    {
        $socialMediaContent->update($request->validated());

        $socialMediaContent->load(['category', 'posts', 'schedules']);

        return new SocialMediaContentResource($socialMediaContent);
    }

    /**
     * Remove the specified social media content.
     */
    public function destroy(SocialMediaContent $socialMediaContent): JsonResponse
    {
        $socialMediaContent->delete();

        return response()->json([
            'message' => 'Content deleted successfully.',
        ], 200);
    }
}
