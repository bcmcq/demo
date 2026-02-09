<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\SocialMediaCategory
 */
class SocialMediaCategoryResource extends JsonResource
{
    protected bool $slimResource = false;

    /**
     * Create a slim resource instance that excludes timestamps.
     */
    public static function slim(mixed $resource): static
    {
        $instance = new static($resource);
        $instance->slimResource = true;

        return $instance;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'contents_count' => $this->whenCounted('contents'),
        ];

        if (! $this->slimResource) {
            $data['created_at'] = $this->created_at;
            $data['updated_at'] = $this->updated_at;
        }

        return $data;
    }
}
