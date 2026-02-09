<?php

namespace App\Http\Controllers\API;

use App\Enums\ContentGenerationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateContentRequest;
use App\Jobs\GenerateContentJob;
use App\Models\ContentGenerationRequest;
use App\Models\SocialMediaContent;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

#[Group('AI Content Generation', weight: 4)]
class AIGenerateController extends Controller
{
    /**
     * Generate content with AI.
     *
     * Queues an AI job to generate new content from a freeform prompt.
     * Returns a generation request ID that can be polled for status.
     */
    #[Endpoint(operationId: 'generateContent')]
    #[Response(202, description: 'Generation job queued.', type: 'array{message: string, generation_request_id: string}')]
    public function __invoke(GenerateContentRequest $request, SocialMediaContent $socialMediaContent): JsonResponse
    {
        $this->authorize('generate', $socialMediaContent);

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
            'generation_request_id' => $generationRequest->id,
        ], 202);
    }
}
