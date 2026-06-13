<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PlanSong extends Pivot
{
    protected $table = 'plan_song';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'team_member_id' => 'integer',
            'category_id' => 'integer',
            'contact_id' => 'integer',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class);
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }
}
