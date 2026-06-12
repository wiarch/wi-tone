<?php

namespace App\Http\Controllers;

use App\Models\Chord;
use Illuminate\View\View;

class ChordBrowserController extends Controller
{
    public function guitar(): View
    {
        return $this->browser('guitar');
    }

    public function keyboard(): View
    {
        return $this->browser('keyboard');
    }

    public function circleOfFifths(): View
    {
        return view('tools.circle-of-fifths');
    }

    public function tuner(): View
    {
        return view('tools.tuner');
    }

    public function metronome(): View
    {
        return view('tools.metronome');
    }

    private function browser(string $instrument): View
    {
        $chords = Chord::query()
            ->whereHas('diagrams', fn ($q) => $q->where('instrument', $instrument))
            ->orderBy('name')
            ->get(['id', 'name', 'root_note']);

        return view('chords.browser', [
            'chords' => $chords,
            'instrument' => $instrument,
            'diagramLibrary' => $this->diagramLibrary(),
        ]);
    }

    /**
     * @return array<string, array{guitar: list<array{variant_name: string, representation: mixed}>, keyboard: list<array{variant_name: string, representation: mixed}>}>
     */
    private function diagramLibrary(): array
    {
        return Chord::query()
            ->with(['diagrams' => fn ($q) => $q->orderBy('variant_name')])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Chord $chord) => [
                $chord->name => [
                    'guitar' => $chord->diagrams
                        ->where('instrument', 'guitar')
                        ->values()
                        ->map(fn ($d) => [
                            'variant_name' => $d->variant_name,
                            'representation' => $d->representation,
                        ])
                        ->all(),
                    'keyboard' => $chord->diagrams
                        ->where('instrument', 'keyboard')
                        ->values()
                        ->map(fn ($d) => [
                            'variant_name' => $d->variant_name,
                            'representation' => $d->representation,
                        ])
                        ->all(),
                ],
            ])
            ->all();
    }
}
