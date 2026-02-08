<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\SocialMediaContent
 */
class SocialMediaContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'title' => $this->title,
            'content' => $this->content,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'is_posted' => $this->posts->isNotEmpty(),
            'is_scheduled' => $this->schedules->isNotEmpty(),
            'posts_count' => $this->posts->count(),
            'schedules_count' => $this->schedules->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
