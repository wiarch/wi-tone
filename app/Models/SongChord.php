<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['song_id', 'instrument', 'content'])]
class SongChord extends Model
{
    use HasFactory;

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
}
