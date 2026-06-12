<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'title', 'artist', 'key', 'category_id'])]
class Song extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function chords(): HasMany
    {
        return $this->hasMany(SongChord::class);
    }

    public function servicePlans(): BelongsToMany
    {
        return $this->belongsToMany(ServicePlan::class, 'plan_song')
            ->using(PlanSong::class)
            ->withPivot('order')
            ->orderByPivot('order')
            ->withTimestamps();
    }
}
