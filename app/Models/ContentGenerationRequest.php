<?php

namespace App\Models;

use App\Enums\ContentGenerationStatus;
use App\Enums\Platform;
use App\Enums\Tone;
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'platform' => Platform::class,
        'tone' => Tone::class,
        'status' => ContentGenerationStatus::class,
        'generated_content' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function socialMediaContent(): BelongsTo
    {
        return $this->belongsTo(SocialMediaContent::class);
    }
}
