<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialMediaContent extends Model
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(SocialMediaCategory::class, (new SocialMediaCategory)->getForeignKey());
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
