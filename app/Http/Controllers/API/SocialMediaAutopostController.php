<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SocialMediaContentResource;
use App\Models\SocialMediaContent;
use App\Services\AutopostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialMediaAutopostController extends Controller
{
    /**
     * Select a random content item using weighted category selection.
     */
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
