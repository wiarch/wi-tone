<?php

namespace App\View\Components;

use App\Support\ChordProParser;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ChordProLine extends Component
{
    /** @var array<int, array{pos: int, name: string}> */
    public array $chords;

    public string $lyrics;

    public string $chordLine;

    /**
     * @param  array{lyrics: string, chords: array<int, array{pos: int, name: string}>}  $line
     */
    public function __construct(public array $line)
    {
        $this->lyrics = $line['lyrics'];
        $this->chords = $line['chords'];
        $this->chordLine = ChordProParser::buildChordLine($this->chords, strlen($this->lyrics));
    }

    public function render(): View|Closure|string
    {
        return view('components.chordpro-line');
    }
}
