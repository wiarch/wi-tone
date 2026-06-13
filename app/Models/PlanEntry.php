<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanEntry extends Model
{
    public const TYPE_SECTION = 'section';

    public const TYPE_SONG = 'song';

    protected $fillable = [
        'service_plan_id',
        'order',
        'type',
        'section_title',
        'song_id',
        'category_id',
        'performance_key',
        'contact_id',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'category_id' => 'integer',
            'contact_id' => 'integer',
            'song_id' => 'integer',
        ];
    }

    public function isSection(): bool
    {
        return $this->type === self::TYPE_SECTION;
    }

    public function isSong(): bool
    {
        return $this->type === self::TYPE_SONG;
    }

    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class);
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
