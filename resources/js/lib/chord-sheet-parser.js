/**
 * Parser de cifrado alineado (línea de acordes + línea de letra).
 * Estilo Ultimate Guitar / SongSelect.
 */

const CHORD_TOKEN =
    /[A-G][#b♯♭]?(?:(?:maj|min|dim|aug|sus[24]?|add\d+)?(?:m|M)?\d*|\d+)*(?:\([^)]+\))?(?:\/[A-G][#b♯♭]?)?/gi;

const SECTION_RE = /^\s*\[[^\]]+\]\s*$/;
const LYRIC_LETTER_RE = /[a-zA-ZàáâãäåèéêëìíîïòóôõöùúûüñçÀÁÂÃÄÅÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÑÇ]/;
const CHORD_NAME_RE =
    /^[A-G][#b♯♭]?(?:(?:maj|min|dim|aug|sus[24]?|add\d+)?(?:m|M)?\d*|\d+)*(?:\([^)]+\))?(?:\/[A-G][#b♯♭]?)?$/i;

export function isChordBracketName(name) {
    return CHORD_NAME_RE.test(String(name).trim());
}

export function sectionLabel(raw) {
    return String(raw).trim().replace(/^\[|\]$/g, '');
}

export function extractChordsFromLine(line) {
    const chords = [];
    const re = new RegExp(CHORD_TOKEN.source, 'gi');
    let match;

    while ((match = re.exec(line)) !== null) {
        chords.push({ pos: match.index, name: match[0] });
    }

    return chords;
}

function stripSectionPrefix(line) {
    const match = line.match(/^(\s*)(\[[^\]]+\])\s*/);
    if (!match) {
        return { prefix: '', body: line, sectionTag: null, prefixEnd: 0 };
    }

    return {
        prefix: match[1] ?? '',
        sectionTag: match[2],
        body: line.slice(match[0].length),
        prefixEnd: match[0].length,
    };
}

function chordLineForDisplay(line) {
    const { sectionTag, body } = stripSectionPrefix(line);
    return sectionTag ? body : line;
}

function chordLineRemainder(line) {
    const { body } = stripSectionPrefix(line);

    return body
        .replace(new RegExp(CHORD_TOKEN.source, 'gi'), '')
        .replace(/[\s/|.:·,;]/g, '');
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

    return chordLineRemainder(line).length === 0;
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

function dedupeChords(chords) {
    const deduped = [];
    const seen = new Set();

    for (const chord of chords) {
        const key = `${chord.pos}:${chord.name}`;
        if (!seen.has(key)) {
            seen.add(key);
            deduped.push(chord);
        }
    }

    return deduped.sort((a, b) => a.pos - b.pos);
}

function chordLineToSpacerLyrics(chordLine) {
    return chordLine
        .replace(new RegExp(CHORD_TOKEN.source, 'gi'), (match) => ' '.repeat(match.length))
        .replace(/[.:·,;]/g, ' ');
}

function alignChordPositions(chordLine, lyricLine) {
    const chordLead = chordLine.match(/^\s*/)?.[0]?.length ?? 0;
    const lyricLead = lyricLine.match(/^\s*/)?.[0]?.length ?? 0;
    const shift = chordLead - lyricLead;

    return extractChordsFromLine(chordLine).map((chord) => ({
        pos: Math.max(0, chord.pos - shift),
        name: chord.name,
    }));
}

function parseChordLyricPair(chordLine, lyricLine) {
    return {
        lyrics: lyricLine,
        chords: dedupeChords(alignChordPositions(chordLine, lyricLine)),
    };
}

function parseChordOnlyLine(chordLine) {
    const chords = extractChordsFromLine(chordLine).map((chord) => ({
        pos: chord.pos,
        name: chord.name,
    }));

    return {
        lyrics: chordLineToSpacerLyrics(chordLine),
        chords: dedupeChords(chords),
    };
}

/**
 * @returns {Array<{lyrics: string, chords: Array<{pos: number, name: string}>}>}
 */
/**
 * Bloques visuales estilo UG (línea de acordes + letra). Omite secciones [Intro], etc.
 * @returns {Array<{type: 'pair', chordLine: string, lyricLine: string} | {type: 'chords', chordLine: string}>}
 */
export function parseChordSheetBlocks(text) {
    const rawLines = text.replace(/\r\n/g, '\n').split('\n');
    const blocks = [];
    let index = 0;

    while (index < rawLines.length) {
        const line = rawLines[index];

        if (!line.trim()) {
            index++;
            continue;
        }

        if (SECTION_RE.test(line.trim())) {
            index++;
            continue;
        }

        if (isChordLine(line)) {
            const next = rawLines[index + 1] ?? '';

            if (isLyricLine(next) && !isChordLine(next)) {
                blocks.push({ type: 'pair', chordLine: chordLineForDisplay(line), lyricLine: next });
                index += 2;
                continue;
            }

            blocks.push({ type: 'chords', chordLine: chordLineForDisplay(line) });
            index++;
            continue;
        }

        index++;
    }

    return blocks;
}

export function buildChordLineFromChords(chords, minLength = 0) {
    if (!chords.length) {
        return '';
    }

    const maxEnd = Math.max(minLength, ...chords.map((c) => c.pos + c.name.length));
    const chars = Array(maxEnd).fill(' ');

    for (const { pos, name } of [...chords].sort((a, b) => a.pos - b.pos)) {
        for (let i = 0; i < name.length; i++) {
            const idx = pos + i;
            if (idx >= 0 && idx < chars.length) {
                chars[idx] = name[i];
            }
        }
    }

    let end = chars.length;
    while (end > 0 && chars[end - 1] === ' ') {
        end--;
    }

    return chars.slice(0, end).join('');
}

export function rebuildChordLine(lyrics, chords) {
    if (!chords.length) {
        return '';
    }

    const maxEnd = Math.max(lyrics.length, ...chords.map((c) => c.pos + c.name.length));
    const chars = Array(maxEnd).fill(' ');

    for (const { pos, name } of [...chords].sort((a, b) => a.pos - b.pos)) {
        for (let i = 0; i < name.length; i++) {
            const idx = pos + i;
            if (idx >= 0 && idx < chars.length) {
                chars[idx] = name[i];
            }
        }
    }

    let end = chars.length;
    while (end > 0 && chars[end - 1] === ' ') {
        end--;
    }

    return chars.slice(0, end).join('');
}

export function blocksToChordPro(blocks) {
    const parsed = blocks.flatMap((block) => {
        if (block.type === 'pair') {
            return [parseChordLyricPair(block.chordLine, block.lyricLine)];
        }
        if (block.type === 'chords') {
            return [parseChordOnlyLine(block.chordLine)];
        }
        if (block.type === 'lyrics') {
            return [{ lyrics: block.lyricLine, chords: [] }];
        }
        return [];
    });

    return parsedLinesToChordPro(parsed);
}

export function blocksToLyricsText(blocks) {
    return blocks
        .filter((b) => b.type === 'pair' || b.type === 'lyrics')
        .map((b) => b.lyricLine)
        .join('\n');
}

export function collectChordsFromBlocks(blocks) {
    const names = new Set();
    const re = new RegExp(CHORD_TOKEN.source, 'gi');

    for (const block of blocks) {
        const line = block.type === 'pair' ? block.chordLine : block.chordLine;
        let match;
        while ((match = re.exec(line)) !== null) {
            names.add(match[0]);
        }
        re.lastIndex = 0;
    }

    return names;
}

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
            output.push({ lyrics: sectionLabel(line.trim()), chords: [] });
            index++;
            continue;
        }

        if (isChordLine(line)) {
            const next = rawLines[index + 1] ?? '';

            if (isLyricLine(next) && !isChordLine(next)) {
                output.push(parseChordLyricPair(line, next));
                index += 2;
                continue;
            }

            output.push(parseChordOnlyLine(line));
            index++;
            continue;
        }

        output.push({ lyrics: line, chords: [] });
        index++;
    }

    return output;
}

export function detectChordSheetFormat(text) {
    const lines = text.replace(/\r\n/g, '\n').split('\n');

    for (let i = 0; i < lines.length; i++) {
        if (isChordLine(lines[i])) {
            return true;
        }
    }

    for (let i = 0; i < lines.length - 1; i++) {
        if (isChordLine(lines[i]) && isLyricLine(lines[i + 1])) {
            return true;
        }
    }

    return false;
}

export function isWhitespaceOnlyLyrics(lyrics) {
    return !String(lyrics ?? '').replace(/\s/g, '').length;
}

export function spreadChordCollisions(chords) {
    if (!chords?.length) {
        return [];
    }

    const sorted = [...chords].sort((a, b) => a.pos - b.pos || a.name.localeCompare(b.name));
    let cursor = -1;

    return sorted.map((chord) => {
        let pos = chord.pos;
        if (pos <= cursor) {
            pos = cursor + 1;
        }
        cursor = pos + chord.name.length;

        return { ...chord, pos };
    });
}

export function normalizeSheetLines(lines) {
    const out = [];

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        const next = lines[i + 1];
        const chordOnly = line.chords?.length && isWhitespaceOnlyLyrics(line.lyrics);
        const lyricOnly = !line.chords?.length && String(line.lyrics ?? '').trim().length > 0;

        if (chordOnly && lyricOnly) {
            out.push({
                lyrics: next.lyrics,
                chords: spreadChordCollisions(line.chords),
            });
            i++;
            continue;
        }

        out.push({
            lyrics: line.lyrics ?? '',
            chords: spreadChordCollisions(line.chords ?? []),
        });
    }

    return out;
}

export function parseChordProLine(line) {
    const chords = [];
    const lyricChars = [];
    let i = 0;

    while (i < line.length) {
        if (line[i] === '[') {
            const end = line.indexOf(']', i + 1);
            if (end !== -1) {
                const name = line.slice(i + 1, end);
                if (isChordBracketName(name)) {
                    chords.push({ pos: lyricChars.length, name });
                    i = end + 1;
                    continue;
                }
            }
        }

        lyricChars.push(line[i]);
        i++;
    }

    return { lyrics: lyricChars.join(''), chords };
}

/**
 * @returns {Array<{type: 'pair', chordLine: string, lyricLine: string} | {type: 'chords', chordLine: string} | {type: 'lyrics', lyricLine: string}>}
 */
export function chordProToBlocks(text) {
    const raw = String(text ?? '').replace(/\r\n/g, '\n');
    if (!raw.trim()) {
        return [];
    }

    const lines = raw.split('\n');
    const blocks = [];

    for (let index = 0; index < lines.length; index++) {
        const parsed = parseChordProLine(lines[index]);
        let { lyrics, chords } = parsed;

        if (!lyrics.trim() && !chords.length) {
            continue;
        }

        chords = spreadChordCollisions(chords);

        const nextParsed = index + 1 < lines.length ? parseChordProLine(lines[index + 1]) : null;
        const chordOnly = chords.length > 0 && isWhitespaceOnlyLyrics(lyrics);
        const nextLyricOnly = nextParsed
            && !nextParsed.chords.length
            && String(nextParsed.lyrics ?? '').trim().length > 0;

        if (chordOnly && nextLyricOnly) {
            blocks.push({
                type: 'pair',
                chordLine: rebuildChordLine(lyrics, chords),
                lyricLine: nextParsed.lyrics,
            });
            index++;
            continue;
        }

        if (chords.length && lyrics.trim()) {
            blocks.push({
                type: 'pair',
                chordLine: rebuildChordLine(lyrics, chords),
                lyricLine: lyrics,
            });
            continue;
        }

        if (chords.length) {
            const chordLine = rebuildChordLine(lyrics, chords);
            if (chordLine.trim()) {
                blocks.push({ type: 'chords', chordLine });
            }
            continue;
        }

        if (lyrics.trim()) {
            blocks.push({ type: 'lyrics', lyricLine: lyrics });
        }
    }

    return blocks;
}

export function parsedLinesToChordPro(parsedLines) {
    return parsedLines
        .map((line) => {
            const sorted = spreadChordCollisions(line.chords);
            let result = '';
            let ci = 0;

            for (let i = 0; i < line.lyrics.length; i++) {
                while (ci < sorted.length && sorted[ci].pos === i) {
                    if (result.length && !result.endsWith(' ')) {
                        result += ' ';
                    }
                    result += `[${sorted[ci].name}]`;
                    ci++;
                }
                result += line.lyrics[i];
            }

            while (ci < sorted.length) {
                if (result.length && !result.endsWith(' ')) {
                    result += ' ';
                }
                result += `[${sorted[ci].name}]`;
                ci++;
            }

            return result;
        })
        .join('\n');
}
