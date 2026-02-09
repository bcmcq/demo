<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialMediaCategoryRequest;
use App\Http\Resources\SocialMediaCategoryResource;
use App\Models\SocialMediaCategory;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Categories', weight: 0)]
class SocialMediaCategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SocialMediaCategory::class, 'social_media_category');
    }

    /**
     * List all categories.
     *
     * Returns a paginated list of social media categories with content counts.
     */
    #[QueryParameter('filter[name]', description: 'Partial match filter on category name.', type: 'string', example: 'tech')]
    #[QueryParameter('sort', description: 'Sort by field. Prefix with - for descending.', type: 'string', example: '-created_at')]
    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 15, example: 25)]
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
     * Get a category.
     *
     * Returns a single category by ID with its content count.
     */
    public function show(SocialMediaCategory $socialMediaCategory): SocialMediaCategoryResource
    {
        $socialMediaCategory->loadCount('contents');

        return new SocialMediaCategoryResource($socialMediaCategory);
    }

    /**
     * Create a category.
     *
     * Creates a new social media category. Requires admin privileges.
     */
    public function store(StoreSocialMediaCategoryRequest $request): JsonResponse
    {
        $category = SocialMediaCategory::query()->create($request->validated());

        return (new SocialMediaCategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Delete a category.
     *
     * Permanently removes a category. Requires admin privileges.
     */
    #[Endpoint(operationId: 'deleteCategory')]
    public function destroy(SocialMediaCategory $socialMediaCategory): JsonResponse
    {
        if ($socialMediaCategory->contents()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a category that still has content. Please remove or reassign the content first.',
            ], 409);
        }

        $socialMediaCategory->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ], 200);
    }
}
