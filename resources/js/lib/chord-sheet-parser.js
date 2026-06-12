/**
 * Parser de cifrado alineado (línea de acordes + línea de letra).
 * Estilo Ultimate Guitar / SongSelect.
 */

const CHORD_TOKEN =
    /[A-G][#b♯♭]?(?:(?:maj|min|dim|aug|sus[24]?|add\d+)?(?:m|M)?\d*|\d+)*(?:\([^)]+\))?(?:\/[A-G][#b♯♭]?)?/gi;

const SECTION_RE = /^\s*\[[^\]]+\]\s*$/;
const LYRIC_LETTER_RE = /[a-zA-ZàáâãäåèéêëìíîïòóôõöùúûüñçÀÁÂÃÄÅÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÑÇ]/;

export function extractChordsFromLine(line) {
    const chords = [];
    const re = new RegExp(CHORD_TOKEN.source, 'gi');
    let match;

    while ((match = re.exec(line)) !== null) {
        chords.push({ pos: match.index, name: match[0] });
    }

    return chords;
}

export function isChordLine(line) {
    const trimmed = line.trim();
    if (!trimmed) {
        return false;
    }

    const chords = extractChordsFromLine(line);
    if (!chords.length) {
        return false;
    }

    const remainder = line
        .replace(new RegExp(CHORD_TOKEN.source, 'gi'), '')
        .replace(/[\s/|]/g, '');

    return remainder.length === 0;
}

export function isLyricLine(line) {
    const trimmed = line.trim();
    if (!trimmed) {
        return false;
    }
    if (SECTION_RE.test(trimmed)) {
        return true;
    }
    if (isChordLine(line)) {
        return false;
    }

    return LYRIC_LETTER_RE.test(line);
}

function wordStarts(lyric) {
    const starts = [];
    const re = /\S+/g;
    let match;

    while ((match = re.exec(lyric)) !== null) {
        starts.push(match.index);
    }

    return starts;
}

function snapToWord(pos, starts) {
    if (!starts.length) {
        return Math.max(0, pos);
    }

    let best = starts[0];
    for (const index of starts) {
        if (index <= pos) {
            best = index;
        } else {
            break;
        }
    }

    return best;
}

function parseChordLyricPair(chordLine, lyricLine) {
    const chordLead = chordLine.match(/^\s*/)?.[0]?.length ?? 0;
    const starts = wordStarts(lyricLine);

    const chords = extractChordsFromLine(chordLine).map((chord) => {
        const rawPos = Math.max(0, chord.pos - chordLead);
        return {
            pos: snapToWord(rawPos, starts),
            name: chord.name,
        };
    });

    const deduped = [];
    const seen = new Set();

    for (const chord of chords) {
        const key = `${chord.pos}:${chord.name}`;
        if (!seen.has(key)) {
            seen.add(key);
            deduped.push(chord);
        }
    }

    return {
        lyrics: lyricLine,
        chords: deduped.sort((a, b) => a.pos - b.pos),
    };
}

/**
 * @returns {Array<{lyrics: string, chords: Array<{pos: number, name: string}>}>}
 */
export function parseChordSheetPaste(text) {
    const rawLines = text.replace(/\r\n/g, '\n').split('\n');
    const output = [];
    let index = 0;

    while (index < rawLines.length) {
        const line = rawLines[index];

        if (!line.trim()) {
            index++;
            continue;
        }

        if (SECTION_RE.test(line.trim())) {
            output.push({ lyrics: line.trim(), chords: [] });
            index++;
            continue;
        }

        if (index + 1 < rawLines.length && isChordLine(line) && isLyricLine(rawLines[index + 1])) {
            output.push(parseChordLyricPair(line, rawLines[index + 1]));
            index += 2;
            continue;
        }

        if (line.includes('[') && line.includes(']')) {
            output.push(parseInlineChordProLine(line));
            index++;
            continue;
        }

        output.push({ lyrics: line, chords: [] });
        index++;
    }

    return output;
}

function parseInlineChordProLine(line) {
    const chords = [];
    const lyricChars = [];
    let i = 0;

    while (i < line.length) {
        if (line[i] === '[') {
            const end = line.indexOf(']', i + 1);
            if (end !== -1) {
                chords.push({ pos: lyricChars.length, name: line.slice(i + 1, end) });
                i = end + 1;
                continue;
            }
        }
        lyricChars.push(line[i]);
        i++;
    }

    return { lyrics: lyricChars.join(''), chords };
}

export function detectChordSheetFormat(text) {
    const lines = text.replace(/\r\n/g, '\n').split('\n');
    let pairs = 0;

    for (let i = 0; i < lines.length - 1; i++) {
        if (isChordLine(lines[i]) && isLyricLine(lines[i + 1])) {
            pairs++;
        }
    }

    return pairs >= 1;
}

export function parsedLinesToChordPro(parsedLines) {
    return parsedLines
        .map((line) => {
            const sorted = [...line.chords].sort((a, b) => a.pos - b.pos);
            let result = '';
            let ci = 0;

            for (let i = 0; i < line.lyrics.length; i++) {
                while (ci < sorted.length && sorted[ci].pos === i) {
                    result += `[${sorted[ci].name}]`;
                    ci++;
                }
                result += line.lyrics[i];
            }

            while (ci < sorted.length) {
                result += `[${sorted[ci].name}]`;
                ci++;
            }

            return result;
        })
        .join('\n');
}
