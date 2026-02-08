<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialMediaCategoryRequest;
use App\Http\Resources\SocialMediaCategoryResource;
use App\Models\SocialMediaCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SocialMediaCategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SocialMediaCategory::class, 'social_media_category');
    }

    /**
     * Display a paginated listing of categories.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(SocialMediaCategory::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
            ])
            ->allowedSorts(['name', 'created_at'])
            ->defaultSort('name')
            ->withCount('contents')
            ->paginate($request->input('per_page', 15));

        return SocialMediaCategoryResource::collection($query);
    }

    /**
     * Display the specified category.
     */
    public function show(SocialMediaCategory $socialMediaCategory): SocialMediaCategoryResource
    {
        $socialMediaCategory->loadCount('contents');

        return new SocialMediaCategoryResource($socialMediaCategory);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreSocialMediaCategoryRequest $request): JsonResponse
    {
        $category = SocialMediaCategory::query()->create($request->validated());

        return (new SocialMediaCategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(SocialMediaCategory $socialMediaCategory): JsonResponse
    {
        $socialMediaCategory->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ], 200);
    }
}
