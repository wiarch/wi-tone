<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'title', 'date', 'notes', 'director_contact_id', 'share_token', 'published_at', 'share_settings'])]
class ServicePlan extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'published_at' => 'datetime',
            'share_settings' => 'array',
            'director_contact_id' => 'integer',
        ];
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && filled($this->share_token);
    }

    public function publicUrl(): ?string
    {
        if (! $this->isPublished()) {
            return null;
        }

        return route('service-plans.public', $this->share_token);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'director_contact_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(PlanEntry::class)->orderBy('order');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'plan_song')
            ->using(PlanSong::class)
            ->withPivot(['order', 'moment_type', 'performance_key', 'team_member_id', 'category_id', 'contact_id'])
            ->orderByPivot('order')
            ->withTimestamps();
    }
}
