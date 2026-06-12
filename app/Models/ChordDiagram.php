<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['chord_id', 'instrument', 'variant_name', 'representation'])]
class ChordDiagram extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'representation' => 'array',
        ];
    }

    public function chord(): BelongsTo
    {
        return $this->belongsTo(Chord::class);
    }
}
