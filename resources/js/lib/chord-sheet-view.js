import { extractChordsFromLine } from './chord-sheet-parser.js';
import { transposeChord } from './chord-diagram-render.js';

const CHORD_IN_LINE = /[A-G][#b♯♭]?(?:(?:maj|min|dim|aug|sus[24]?|add\d+)?(?:m|M)?\d*|\d+)*(?:\([^)]+\))?(?:\/[A-G][#b♯♭]?)?/gi;

export function readSongContent(root) {
    const el = root.querySelector('[data-song-content]');
    if (!el?.textContent?.trim()) {
        return '';
    }

    return JSON.parse(el.textContent);
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

export function transposeChordLine(chordLine, semitones) {
    if (!semitones) {
        return chordLine;
    }

    return chordLine.replace(new RegExp(CHORD_IN_LINE.source, 'gi'), (match) => transposeChord(match, semitones));
}

export function transposeBlocks(blocks, semitones) {
    if (!semitones) {
        return blocks;
    }

    return blocks.map((block) => {
        if (block.type === 'pair' || block.type === 'chords') {
            return {
                ...block,
                chordLine: transposeChordLine(block.chordLine, semitones),
            };
        }

        return { ...block };
    });
}

export function renderChordRowHtml(chordLine, { minWidth = 0, blockIndex = 0, interactive = false, originalChordLine = null, chordClass = 'text-amber-400' } = {}) {
    const chords = extractChordsFromLine(chordLine);
    const originalChords = originalChordLine ? extractChordsFromLine(originalChordLine) : chords;
    const width = Math.max(chordLine.length, minWidth, ...chords.map((c) => c.pos + c.name.length), 1);

    const chordMarkup = chords
        .map((chord, chordIndex) => {
            const diagramChord = originalChords[chordIndex]?.name ?? chord.name;

            if (interactive) {
                return `<span
                    data-sheet-chord
                    data-block="${blockIndex}"
                    data-chord-idx="${chordIndex}"
                    class="absolute top-0 inline-flex cursor-grab touch-none select-none items-center rounded bg-amber-500/15 px-0.5 font-semibold text-amber-400 hover:bg-amber-500/25 active:cursor-grabbing"
                    style="left:${chord.pos}ch"
                >${escapeHtml(chord.name)}<button type="button" data-remove-sheet-chord data-block="${blockIndex}" data-chord-idx="${chordIndex}" class="ml-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded text-sm font-bold leading-none text-amber-500 hover:bg-amber-500/25 hover:text-white" aria-label="Quitar">×</button></span>`;
            }

            return `<span
                class="chord-trigger absolute top-0 inline-flex cursor-default select-none items-center rounded bg-amber-500/15 px-0.5 font-semibold ${chordClass}"
                style="left:${chord.pos}ch"
                data-chord="${escapeHtml(diagramChord)}"
                role="button"
                tabindex="0"
            >${escapeHtml(chord.name)}</span>`;
        })
        .join('');

    return `<div data-chord-row="${blockIndex}" class="relative min-h-[1.5rem] font-mono text-sm leading-none">
        <span class="invisible whitespace-pre select-none" aria-hidden="true">${' '.repeat(width)}</span>
        <div class="absolute inset-0 top-0">${chordMarkup}</div>
    </div>`;
}

export function renderBlocksHtml(blocks, { showChords = true, showLyrics = true, interactive = false, originalBlocks = null, lyricClass = 'text-slate-200', chordClass = 'text-amber-400' } = {}) {
    return blocks
        .map((block, blockIndex) => {
            const original = originalBlocks?.[blockIndex];
            const originalChordLine = original && (original.type === 'pair' || original.type === 'chords')
                ? original.chordLine
                : block.chordLine;

            if (block.type === 'pair') {
                const chordHtml = showChords
                    ? renderChordRowHtml(block.chordLine, {
                        minWidth: block.lyricLine.length,
                        blockIndex,
                        interactive,
                        originalChordLine,
                        chordClass,
                    })
                    : '';

                const lyricHtml = showLyrics
                    ? `<div class="mt-1 whitespace-pre-wrap font-mono text-sm leading-relaxed ${lyricClass}">${escapeHtml(block.lyricLine)}</div>`
                    : '';

                if (!chordHtml && !lyricHtml) {
                    return '';
                }

                return `<div data-sheet-block="${blockIndex}" class="sheet-pair mb-5 break-inside-avoid">${chordHtml}${lyricHtml}</div>`;
            }

            if (block.type === 'chords' && showChords) {
                return `<div data-sheet-block="${blockIndex}" class="sheet-chords mb-4 break-inside-avoid">${renderChordRowHtml(block.chordLine, { blockIndex, interactive, originalChordLine, chordClass })}</div>`;
            }

            if (block.type === 'lyrics' && showLyrics) {
                return `<div data-sheet-block="${blockIndex}" class="sheet-lyrics mb-4 whitespace-pre-wrap font-mono text-sm leading-relaxed ${lyricClass} break-inside-avoid">${escapeHtml(block.lyricLine)}</div>`;
            }

            return '';
        })
        .join('');
}
