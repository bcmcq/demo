<?php

namespace App\Http\Controllers\API;

use App\Enums\ContentGenerationStatus;
use App\Http\Controllers\Controller;
use App\Models\ContentGenerationRequest;
use App\Models\SocialMediaContent;
use Illuminate\Http\JsonResponse;

class AIStatusController extends Controller
{
    /**
     * Return the current status of a content generation request.
     */
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
