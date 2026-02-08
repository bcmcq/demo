<?php

namespace App\Models;

use App\Enums\ContentGenerationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentGenerationRequest extends Model
{
    /** @use HasFactory<\Database\Factories\ContentGenerationRequestFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'type',
        'prompt',
        'platform',
        'tone',
        'status',
        'generated_content',
        'error',
        'social_media_content_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ContentGenerationStatus::class,
            'generated_content' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function socialMediaContent(): BelongsTo
    {
        return $this->belongsTo(SocialMediaContent::class);
    }
}
