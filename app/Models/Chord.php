<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'root_note'])]
class Chord extends Model
{
    use HasFactory;

    public function diagrams(): HasMany
    {
        return $this->hasMany(ChordDiagram::class);
    }

    public function guitarDiagrams(): HasMany
    {
        return $this->diagrams()->where('instrument', 'guitar');
    }

    public function keyboardDiagrams(): HasMany
    {
        return $this->diagrams()->where('instrument', 'keyboard');
    }
}
