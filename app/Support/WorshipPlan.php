<?php

namespace App\Support;

class WorshipPlan
{
    /** @var list<string> */
    public const ROLES = [
        'Director',
        'Vocalista',
        'Guitarrista',
        'Baterista',
        'Tecladista',
        'Bajista',
        'Proyección',
        'Sonido',
        'Otro',
    ];

    /** @var list<string> */
    public const VOCAL_RANGES = [
        'Soprano',
        'Mezzosoprano',
        'Contralto',
        'Alto',
        'Tenor',
        'Barítono',
        'Bajo',
        'No aplica',
    ];

    /** @var list<string> */
    public const VOICE_TONES = [
        'Soprano',
        'Alto',
        'Contralto',
        'Tenor',
        'Barítono',
        'Bajo',
    ];

    /** @var list<string> */
    public const MOMENT_TYPES = [
        'Himno',
        'Canción',
        'Adoración',
        'Ofrenda',
        'Comunión',
        'Presentación',
        'Otro',
    ];

    /** @var list<string> */
    public const MUSICAL_KEYS = [
        'A', 'Bb', 'B', 'C', 'Db', 'D', 'Eb', 'E', 'F', 'F#', 'G', 'Ab',
    ];
}
