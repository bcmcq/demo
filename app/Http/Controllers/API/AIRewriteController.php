<?php

namespace App\Http\Controllers\API;

use App\Enums\ContentGenerationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RewriteContentRequest;
use App\Jobs\GenerateContentJob;
use App\Models\ContentGenerationRequest;
use App\Models\SocialMediaContent;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

#[Group('AI Content Generation', weight: 4)]
class AIRewriteController extends Controller
{
    /**
     * Rewrite content with AI.
     *
     * Queues an AI job to rewrite existing content for a specific platform and tone.
     * Returns a generation request ID that can be polled for status.
     */
    #[Endpoint(operationId: 'rewriteContent')]
    #[Response(202, description: 'Rewrite job queued.', type: 'array{message: string, generation_request_id: string}')]
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
