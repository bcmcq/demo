<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class SocialMediaPost extends Model
{
    use HasFactory;

    /** {@inheritdoc} */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'social_media_content_id',
        'posted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(SocialMediaContent::class, 'social_media_content_id');
    }

    public function media(): HasManyThrough
    {
        return $this->hasManyThrough(
            Media::class,
            SocialMediaContent::class,
            'id',
            'social_media_content_id',
            'social_media_content_id',
            'id'
        );
    }
}
