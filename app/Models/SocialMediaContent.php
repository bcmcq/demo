<?php

namespace App\Models;

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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @ai accepted this change but this needs to be reviewed, looks like we're missing a FK or index here.
     */
    public function category(): BelongsTo
    {
        // TODO: Review this, it looks like we're missing some database FK's or indexes here.
        return $this->belongsTo(SocialMediaCategory::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialMediaPost::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SocialMediaSchedule::class);
    }
}
