<?php

namespace Database\Seeders;

use App\Models\Chord;
use App\Models\ChordDiagram;
use Illuminate\Database\Seeder;

class ChordDictionarySeeder extends Seeder
{
    private const VARIANT = 'default';

    public function run(): void
    {
        foreach ($this->dictionary() as $entry) {
            $chord = Chord::updateOrCreate(
                ['name' => $entry['name']],
                ['root_note' => $entry['root_note']],
            );

            ChordDiagram::updateOrCreate(
                [
                    'chord_id' => $chord->id,
                    'instrument' => 'guitar',
                    'variant_name' => self::VARIANT,
                ],
                ['representation' => $entry['guitar']],
            );

            ChordDiagram::updateOrCreate(
                [
                    'chord_id' => $chord->id,
                    'instrument' => 'keyboard',
                    'variant_name' => self::VARIANT,
                ],
                ['representation' => $entry['keyboard']],
            );
        }
    }

    /**
     * Guitar: frets from 6th string to 1st (-1 = muted).
     * Keyboard: exact note names in the chord.
     *
     * @return list<array{name: string, root_note: string, guitar: list<int>, keyboard: list<string>}>
     */
    private function dictionary(): array
    {
        return [
            // C
            [
                'name' => 'C',
                'root_note' => 'C',
                'guitar' => [-1, 3, 2, 0, 1, 0],
                'keyboard' => ['C', 'E', 'G'],
            ],
            [
                'name' => 'Cm',
                'root_note' => 'C',
                'guitar' => [-1, 3, 5, 5, 4, 3],
                'keyboard' => ['C', 'Eb', 'G'],
            ],
            [
                'name' => 'C7',
                'root_note' => 'C',
                'guitar' => [-1, 3, 2, 3, 1, 0],
                'keyboard' => ['C', 'E', 'G', 'Bb'],
            ],

            // D
            [
                'name' => 'D',
                'root_note' => 'D',
                'guitar' => [-1, -1, 0, 2, 3, 2],
                'keyboard' => ['D', 'F#', 'A'],
            ],
            [
                'name' => 'Dm',
                'root_note' => 'D',
                'guitar' => [-1, -1, 0, 2, 3, 1],
                'keyboard' => ['D', 'F', 'A'],
            ],
            [
                'name' => 'D7',
                'root_note' => 'D',
                'guitar' => [-1, -1, 0, 2, 1, 2],
                'keyboard' => ['D', 'F#', 'A', 'C'],
            ],

            // E
            [
                'name' => 'E',
                'root_note' => 'E',
                'guitar' => [0, 2, 2, 1, 0, 0],
                'keyboard' => ['E', 'G#', 'B'],
            ],
            [
                'name' => 'Em',
                'root_note' => 'E',
                'guitar' => [0, 2, 2, 0, 0, 0],
                'keyboard' => ['E', 'G', 'B'],
            ],
            [
                'name' => 'E7',
                'root_note' => 'E',
                'guitar' => [0, 2, 0, 1, 0, 0],
                'keyboard' => ['E', 'G#', 'B', 'D'],
            ],

            // F
            [
                'name' => 'F',
                'root_note' => 'F',
                'guitar' => [1, 3, 3, 2, 1, 1],
                'keyboard' => ['F', 'A', 'C'],
            ],
            [
                'name' => 'Fm',
                'root_note' => 'F',
                'guitar' => [1, 3, 3, 1, 1, 1],
                'keyboard' => ['F', 'Ab', 'C'],
            ],
            [
                'name' => 'F7',
                'root_note' => 'F',
                'guitar' => [1, 3, 1, 2, 1, 1],
                'keyboard' => ['F', 'A', 'C', 'Eb'],
            ],

            // G
            [
                'name' => 'G',
                'root_note' => 'G',
                'guitar' => [3, 2, 0, 0, 3, 3],
                'keyboard' => ['G', 'B', 'D'],
            ],
            [
                'name' => 'Gm',
                'root_note' => 'G',
                'guitar' => [3, 5, 5, 3, 3, 3],
                'keyboard' => ['G', 'Bb', 'D'],
            ],
            [
                'name' => 'G7',
                'root_note' => 'G',
                'guitar' => [3, 2, 0, 0, 0, 1],
                'keyboard' => ['G', 'B', 'D', 'F'],
            ],

            // A
            [
                'name' => 'A',
                'root_note' => 'A',
                'guitar' => [-1, 0, 2, 2, 2, 0],
                'keyboard' => ['A', 'C#', 'E'],
            ],
            [
                'name' => 'Am',
                'root_note' => 'A',
                'guitar' => [-1, 0, 2, 2, 1, 0],
                'keyboard' => ['A', 'C', 'E'],
            ],
            [
                'name' => 'A7',
                'root_note' => 'A',
                'guitar' => [-1, 0, 2, 0, 2, 0],
                'keyboard' => ['A', 'C#', 'E', 'G'],
            ],

            // B
            [
                'name' => 'B',
                'root_note' => 'B',
                'guitar' => [-1, 2, 4, 4, 4, 2],
                'keyboard' => ['B', 'D#', 'F#'],
            ],
            [
                'name' => 'Bm',
                'root_note' => 'B',
                'guitar' => [-1, 2, 4, 4, 3, 2],
                'keyboard' => ['B', 'D', 'F#'],
            ],
            [
                'name' => 'B7',
                'root_note' => 'B',
                'guitar' => [-1, 2, 1, 2, 0, 2],
                'keyboard' => ['B', 'D#', 'F#', 'A'],
            ],

            // Extendido (ejemplo)
            [
                'name' => 'C#m7',
                'root_note' => 'C#',
                'guitar' => [-1, 4, 6, 4, 5, 4],
                'keyboard' => ['C#', 'E', 'G#', 'B'],
            ],
        ];
    }
}
