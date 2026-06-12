<?php

namespace App\Support;

class ChordProParser
{
    /**
     * @return array<int, array{lyrics: string, chords: array<int, array{pos: int, name: string}>}>
     */
    public static function parse(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];

        return array_map(fn (string $line) => self::parseLine($line), $lines);
    }

    /**
     * @return array{lyrics: string, chords: array<int, array{pos: int, name: string}>}
     */
    public static function parseLine(string $line): array
    {
        $chords = [];
        $lyrics = '';
        $length = strlen($line);
        $i = 0;

        while ($i < $length) {
            if ($line[$i] === '[') {
                $end = strpos($line, ']', $i + 1);
                if ($end !== false) {
                    $chords[] = [
                        'pos' => strlen($lyrics),
                        'name' => substr($line, $i + 1, $end - $i - 1),
                    ];
                    $i = $end + 1;

                    continue;
                }
            }

            $lyrics .= $line[$i];
            $i++;
        }

        return ['lyrics' => $lyrics, 'chords' => $chords];
    }

    /**
     * @param  array<int, array{pos: int, name: string}>  $chords
     */
    public static function buildChordLine(array $chords, int $lyricLength): string
    {
        if ($chords === []) {
            return '';
        }

        $width = $lyricLength;
        foreach ($chords as $chord) {
            $width = max($width, $chord['pos'] + strlen($chord['name']));
        }

        $chars = array_fill(0, $width, ' ');

        foreach ($chords as $chord) {
            $name = $chord['name'];
            for ($j = 0; $j < strlen($name); $j++) {
                $idx = $chord['pos'] + $j;
                if ($idx < count($chars)) {
                    $chars[$idx] = $name[$j];
                }
            }
        }

        return rtrim(implode('', $chars));
    }

    /**
     * @return array<int, string>
     */
    public static function extractChordNames(string $text): array
    {
        preg_match_all('/\[([^\]]+)\]/', $text, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }
}
