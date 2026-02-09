<?php

namespace App\Http\Controllers\API;

use App\Enums\ContentGenerationStatus;
use App\Http\Controllers\Controller;
use App\Models\ContentGenerationRequest;
use App\Models\SocialMediaContent;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

#[Group('AI Content Generation', weight: 4)]
class AIStatusController extends Controller
{
    /**
     * Check generation status.
     *
     * Polls the status of an AI content generation or rewrite request.
     * When completed, the response includes `generated_content`.
     * When failed, the response includes `error`.
     */
    #[Endpoint(operationId: 'generationStatus')]
    #[Response(200, description: 'Generation request status.', type: 'array{id: string, type: string, platform: string, tone: string, status: string, generated_content?: string, error?: string}')]
    public function __invoke(ContentGenerationRequest $contentGenerationRequest): JsonResponse
    {
        $this->authorize('statusCheck', [SocialMediaContent::class, $contentGenerationRequest]);

        $data = [
            'id' => $contentGenerationRequest->id,
            'type' => $contentGenerationRequest->type,
            'platform' => $contentGenerationRequest->platform,
            'tone' => $contentGenerationRequest->tone,
            'status' => $contentGenerationRequest->status->value,
        ];

        if ($contentGenerationRequest->status === ContentGenerationStatus::Completed) {
            $data['generated_content'] = $contentGenerationRequest->generated_content;
        }

        if ($contentGenerationRequest->status === ContentGenerationStatus::Failed) {
            $data['error'] = $contentGenerationRequest->error;
        }

        return response()->json($data);
    }
}
