<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SocialMediaContentResource;
use App\Models\SocialMediaContent;
use App\Services\AutopostService;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Content', weight: 1)]
class SocialMediaAutopostController extends Controller
{
    /**
     * Autopost selection.
     *
     * Selects a content item using weighted random category selection.
     * Returns the selected content or 404 if no eligible content exists.
     *
     * @response SocialMediaContentResource
     */
    #[Endpoint(operationId: 'autopost')]
    #[Response(404, description: 'No available content found for autopost.', type: 'array{message: string}')]
    public function __invoke(Request $request, AutopostService $autopostService): JsonResponse|SocialMediaContentResource
    {
        $this->authorize('autopost', SocialMediaContent::class);

        $content = $autopostService->selectContent($request->user()->account_id);

        if ($content === null) {
            return response()->json([
                'message' => 'No available content found for autopost.',
            ], 404);
        }

        $content->load(['category']);

        return new SocialMediaContentResource($content);
    }
}
