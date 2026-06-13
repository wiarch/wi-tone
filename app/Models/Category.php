<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'slug', 'is_system'])]
class Category extends Model
{
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    /**
     * @param  Builder<Category>  $query
     */
    public function scopeForUser(Builder $query, int $userId): void
    {
        $query->where(function (Builder $inner) use ($userId) {
            $inner->whereNull('user_id')
                ->orWhere('user_id', $userId);
        });
    }
}
