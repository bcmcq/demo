<?php

namespace App\Http\Controllers\API;

use App\Enums\ContentGenerationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateContentRequest;
use App\Jobs\GenerateContentJob;
use App\Models\ContentGenerationRequest;
use App\Models\SocialMediaContent;
use Illuminate\Http\JsonResponse;

class AIGenerateController extends Controller
{
    /**
     * Dispatch an AI content generation job from a freeform prompt.
     */
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
