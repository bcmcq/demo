<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialMediaContent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'social_media_category_id',
        'title',
        'content',
    ];

    protected static function booted(): void
    {
        static::deleting(function (SocialMediaContent $content) {
            $content->media->each->delete();
        });
    }

    /* -------- Relationships -------- */

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SocialMediaCategory::class, 'social_media_category_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialMediaPost::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SocialMediaSchedule::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * AI rewrite requests for this content.
     */
    public function rewrites(): HasMany
    {
        return $this->hasMany(ContentGenerationRequest::class)->where('type', 'rewrite');
    }

    /* -------- Query Scopes -------- */

    /**
     * Scope to content belonging to a specific account.
     */
    public function scopeForAccount(Builder $query, int $accountId): Builder
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope to content belonging to a specific category.
     */
    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('social_media_category_id', $categoryId);
    }

    /**
     * Scope to content that has been posted at least once.
     */
    public function scopePosted(Builder $query): Builder
    {
        return $query->whereHas('posts');
    }

    /**
     * Scope to content that has never been posted.
     */
    public function scopeUnposted(Builder $query): Builder
    {
        return $query->whereDoesntHave('posts');
    }

    /**
     * Scope to content that has been scheduled at least once.
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->whereHas('schedules');
    }

    /**
     * Scope to content that has never been scheduled.
     */
    public function scopeUnscheduled(Builder $query): Builder
    {
        return $query->whereDoesntHave('schedules');
    }

    /**
     * Scope to content that is available for autopost: neither posted nor scheduled.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->unposted()->unscheduled();
    }
}
