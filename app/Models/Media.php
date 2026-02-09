<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'social_media_content_id',
        'type',
        'file_path',
        'file_name',
        'mime_type',
        'size',
        'mux_asset_id',
        'mux_playback_id',
        'mux_upload_id',
        'thumbnail_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'type' => MediaType::class,
        'size' => 'integer',
    ];

    public function socialMediaContent(): BelongsTo
    {
        return $this->belongsTo(SocialMediaContent::class);
    }

    public function isImage(): bool
    {
        return $this->type === MediaType::Image;
    }

    public function isVideo(): bool
    {
        return $this->type === MediaType::Video;
    }

    /**
     * Get the S3 URL for the media file.
     */
    public function getUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return $this->replaceMinioUrl(Storage::disk('s3')->url($this->file_path));
    }

    /**
     * Get the Mux HLS playback URL for video media.
     */
    public function getPlaybackUrlAttribute(): ?string
    {
        if (! $this->isVideo() || ! $this->mux_playback_id) {
            return null;
        }

        return "https://stream.mux.com/{$this->mux_playback_id}.m3u8";
    }

    /**
     * Get the thumbnail URL: prefer local S3 thumbnail, fall back to Mux thumbnail.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_path) {
            return $this->replaceMinioUrl(Storage::disk('s3')->url($this->thumbnail_path));
        }

        if ($this->isVideo() && $this->mux_playback_id) {
            return "https://image.mux.com/{$this->mux_playback_id}/thumbnail.jpg";
        }

        return null;
    }

    private function replaceMinioUrl(string $url): string
    {
        return str_replace('http://minio:9000', 'http://localhost:9002', $url);
    }
}
