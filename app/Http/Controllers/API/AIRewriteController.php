<?php

namespace App\Http\Controllers\API;

use App\Enums\ContentGenerationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RewriteContentRequest;
use App\Jobs\GenerateContentJob;
use App\Models\ContentGenerationRequest;
use App\Models\SocialMediaContent;
use Illuminate\Http\JsonResponse;

class AIRewriteController extends Controller
{
    /**
     * Dispatch an AI rewrite job for the given content item.
     */
    public function __invoke(RewriteContentRequest $request, SocialMediaContent $socialMediaContent): JsonResponse
    {
        $this->authorize('rewrite', $socialMediaContent);

        $generationRequest = ContentGenerationRequest::query()->create([
            'account_id' => $request->user()->account_id,
            'type' => 'rewrite',
            'prompt' => "Rewrite: {$socialMediaContent->title}",
            'platform' => $request->validated('platform'),
            'tone' => $request->validated('tone'),
            'status' => ContentGenerationStatus::Pending,
            'social_media_content_id' => $socialMediaContent->id,
        ]);

        GenerateContentJob::dispatch($generationRequest);

        return response()->json([
            'message' => 'Content rewrite has been queued.',
            'generation_request_id' => $generationRequest->id,
        ], 202);
    }
}
