<?php

namespace App\Http\Controllers\API;

use App\Enums\ContentGenerationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateContentRequest;
use App\Http\Requests\RewriteContentRequest;
use App\Http\Resources\ContentGenerationRequestResource;
use App\Jobs\GenerateContentJob;
use App\Models\ContentGenerationRequest;
use App\Models\SocialMediaContent;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

#[Group('AI Content Generation', weight: 4)]
class AIContentController extends Controller
{
    /**
     * Generate content with AI.
     *
     * Queues an AI job to generate new content from a freeform prompt.
     * Returns a generation request ID that can be polled for status.
     */
    #[Endpoint(operationId: 'generateContent')]
    #[Response(202, description: 'Generation job queued.', type: 'array{message: string, generation_request_id: string}')]
    public function generate(GenerateContentRequest $request): JsonResponse
    {
        $this->authorize('generate', SocialMediaContent::class);

        $generationRequest = ContentGenerationRequest::query()->create([
            'account_id' => $request->user()->account_id,
            'type' => 'generate',
            'prompt' => $request->validated('prompt'),
            'platform' => $request->validated('platform'),
            'tone' => $request->validated('tone'),
            'status' => ContentGenerationStatus::Pending,
        ]);

        GenerateContentJob::dispatch($generationRequest);

        return response()->json([
            'message' => 'Content generation has been queued.',
            'data' => [
                'generation_request_id' => $generationRequest->id,
            ],
        ], 202);
    }

    /**
     * Rewrite content with AI.
     *
     * Queues an AI job to rewrite existing content for a specific platform and tone.
     * Returns a generation request ID that can be polled for status.
     */
    #[Endpoint(operationId: 'rewriteContent')]
    #[Response(202, description: 'Rewrite job queued.', type: 'array{message: string, generation_request_id: string}')]
    public function rewrite(RewriteContentRequest $request, SocialMediaContent $socialMediaContent): JsonResponse
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
            'data' => [
                'generation_request_id' => $generationRequest->id,
            ],
        ], 202);
    }

    /**
     * Check generation status.
     *
     * Polls the status of an AI content generation or rewrite request.
     * When completed, the response includes `generated_content`.
     * When failed, the response includes `error`.
     */
    #[Endpoint(operationId: 'generationStatus')]
    #[Response(200, description: 'Generation request status.', type: 'array{id: string, type: string, platform: string, tone: string, status: string, generated_content?: string, error?: string}')]
    public function status(ContentGenerationRequest $contentGenerationRequest): ContentGenerationRequestResource
    {
        $this->authorize('statusCheck', [SocialMediaContent::class, $contentGenerationRequest]);

        return new ContentGenerationRequestResource($contentGenerationRequest);
    }
}
