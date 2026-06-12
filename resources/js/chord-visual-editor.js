/**
 * Editor visual de cifrado — drag & drop de acordes sobre la letra.
 */

import {
    detectChordSheetFormat,
    parseChordSheetPaste,
    parsedLinesToChordPro,
} from './lib/chord-sheet-parser.js';

function parseChordProLine(line) {
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

function chordProToLines(text, paletteSet = null) {
    const raw = text.replace(/\r\n/g, '\n');
    if (!raw.trim()) {
        return [{ segments: [{ text: '', chord: null }] }];
    }

    return raw.split('\n').map((line) => {
        const { lyrics, chords } = parseChordProLine(line);
        const segments = tokenizeLine(lyrics);
        applyChordsToSegments(segments, chords);
        chords.forEach((c) => paletteSet?.add(c.name));

        return { segments };
    });
}

function tokenizeLine(lyrics) {
    if (!lyrics) {
        return [{ text: '', chord: null }];
    }

    const parts = lyrics.match(/\S+|\s+/g) ?? [];

    return parts.map((text) => ({ text, chord: null }));
}

function applyChordsToSegments(segments, chords) {
    for (const chord of chords) {
        let offset = 0;
        let placed = false;

        for (const seg of segments) {
            if (offset === chord.pos) {
                seg.chord = chord.name;
                placed = true;
                break;
            }
            offset += seg.text.length;
        }

        if (!placed) {
            const totalLen = segments.reduce((sum, s) => sum + s.text.length, 0);
            if (chord.pos === totalLen && segments.length) {
                segments[segments.length - 1].chord = chord.name;
            }
        }
    }
}

function linesToLyricsText(lines) {
    return lines.map((line) => line.segments.map((s) => s.text).join('')).join('\n');
}

function serializeToChordPro(lines) {
    return lines
        .map((line) => {
            let lyrics = '';
            const chordEvents = [];

            for (const seg of line.segments) {
                if (seg.chord) {
                    chordEvents.push({ pos: lyrics.length, name: seg.chord });
                }
                lyrics += seg.text;
            }

            chordEvents.sort((a, b) => a.pos - b.pos);

            let result = '';
            let ci = 0;

            for (let i = 0; i < lyrics.length; i++) {
                while (ci < chordEvents.length && chordEvents[ci].pos === i) {
                    result += `[${chordEvents[ci].name}]`;
                    ci++;
                }
                result += lyrics[i];
            }

            while (ci < chordEvents.length) {
                result += `[${chordEvents[ci].name}]`;
                ci++;
            }

            return result;
        })
        .join('\n');
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function initVisualChordEditor(root) {
    const searchUrl = root.dataset.searchUrl;
    const lyricsSource = root.querySelector('[data-lyrics-source]');
    const canvas = root.querySelector('[data-visual-canvas]');
    const paletteEl = root.querySelector('[data-chord-palette]');
    const contentField = root.querySelector('[data-content-field]');
    const floater = root.querySelector('[data-chord-floater]');
    const searchInput = root.querySelector('[data-chord-search]');
    const resultsList = root.querySelector('[data-chord-results]');
    const statusEl = root.querySelector('[data-chord-status]');
    const initialEl = root.querySelector('[data-initial-content]');
    const importStatusEl = root.querySelector('[data-import-status]');
    const form = root.closest('form');

    if (!lyricsSource || !canvas || !paletteEl || !contentField) {
        return;
    }

    let lines = [{ segments: [{ text: '', chord: null }] }];
    let searchChords = [];
    let searchActiveIndex = 0;
    let debounceTimer = null;
    let floaterOpen = false;
    let draggedChord = null;
    let dragSource = null;

    const paletteChords = new Set();

    function loadInitial() {
        paletteChords.clear();
        const initial = initialEl?.value ?? '';
        lines = chordProToLines(initial, paletteChords);
        lyricsSource.value = linesToLyricsText(lines);
        renderAll();
    }

    function renderPalette() {
        const items = [...paletteChords].sort((a, b) => a.localeCompare(b));

        if (!items.length) {
            paletteEl.innerHTML = '<p class="text-xs text-slate-500">Arrastra acordes desde «+ Añadir» o suéltalos sobre la letra.</p>';
            return;
        }

        paletteEl.innerHTML = items
            .map(
                (name) => `<span
                    draggable="true"
                    data-palette-chord="${escapeHtml(name)}"
                    class="chord-palette-item inline-flex cursor-grab select-none items-center rounded-lg border border-violet-500/30 bg-violet-600/20 px-3 py-1.5 font-mono text-sm font-semibold text-violet-200 transition hover:bg-violet-600/35 active:cursor-grabbing"
                >${escapeHtml(name)}</span>`
            )
            .join('');

        paletteEl.querySelectorAll('[data-palette-chord]').forEach((el) => {
            el.addEventListener('dragstart', (e) => {
                draggedChord = el.dataset.paletteChord;
                dragSource = { type: 'palette' };
                e.dataTransfer.setData('text/plain', draggedChord);
                e.dataTransfer.effectAllowed = 'copyMove';
            });
            el.addEventListener('dragend', () => {
                draggedChord = null;
                dragSource = null;
            });
        });
    }

    function renderCanvas() {
        if (!lines.length) {
            lines = [{ segments: [{ text: '', chord: null }] }];
        }

        canvas.innerHTML = lines
            .map((line, lineIndex) => {
                const units = line.segments
                    .map((seg, segIndex) => {
                        const chordHtml = seg.chord
                            ? `<span
                                draggable="true"
                                data-canvas-chord="${escapeHtml(seg.chord)}"
                                data-line="${lineIndex}"
                                data-segment="${segIndex}"
                                class="chord-badge inline-flex cursor-grab items-center gap-1 rounded-md bg-violet-600 px-1.5 py-0.5 font-mono text-xs font-semibold text-white shadow-sm active:cursor-grabbing"
                            >${escapeHtml(seg.chord)}<button type="button" data-remove-chord data-line="${lineIndex}" data-segment="${segIndex}" class="ml-0.5 rounded text-violet-200/80 hover:text-white" aria-label="Quitar acorde">×</button></span>`
                            : '<span class="chord-placeholder text-[10px] text-transparent select-none">·</span>';

                        return `<span
                            class="chord-drop-unit inline-flex flex-col items-center align-bottom"
                            data-drop-unit
                            data-line="${lineIndex}"
                            data-segment="${segIndex}"
                        >
                            <span data-chord-slot class="flex min-h-[22px] min-w-[1ch] items-end justify-center px-px transition-colors">${chordHtml}</span>
                            <span class="font-mono text-sm leading-relaxed text-slate-200 whitespace-pre">${escapeHtml(seg.text) || '&nbsp;'}</span>
                        </span>`;
                    })
                    .join('');

                return `<div data-visual-line data-line-index="${lineIndex}" class="mb-3 flex flex-wrap items-end gap-y-1 leading-relaxed">${units || '&nbsp;'}</div>`;
            })
            .join('');

        bindCanvasEvents();
        syncHiddenField();
    }

    function bindCanvasEvents() {
        canvas.querySelectorAll('[data-drop-unit]').forEach((unit) => {
            unit.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = dragSource?.type === 'canvas' ? 'move' : 'copy';
                unit.querySelector('[data-chord-slot]')?.classList.add('ring-2', 'ring-violet-500/60', 'rounded');
            });
            unit.addEventListener('dragleave', () => {
                unit.querySelector('[data-chord-slot]')?.classList.remove('ring-2', 'ring-violet-500/60', 'rounded');
            });
            unit.addEventListener('drop', (e) => {
                e.preventDefault();
                unit.querySelector('[data-chord-slot]')?.classList.remove('ring-2', 'ring-violet-500/60', 'rounded');

                const chord = e.dataTransfer.getData('text/plain') || draggedChord;
                if (!chord) {
                    return;
                }

                const lineIndex = Number(unit.dataset.line);
                const segIndex = Number(unit.dataset.segment);

                if (dragSource?.type === 'canvas') {
                    const fromLine = dragSource.line;
                    const fromSeg = dragSource.segment;
                    const fromChord = lines[fromLine]?.segments[fromSeg]?.chord;
                    if (fromChord && (fromLine !== lineIndex || fromSeg !== segIndex)) {
                        lines[fromLine].segments[fromSeg].chord = null;
                    }
                }

                lines[lineIndex].segments[segIndex].chord = chord;
                paletteChords.add(chord);
                renderAll();
            });
        });

        canvas.querySelectorAll('[data-canvas-chord]').forEach((badge) => {
            badge.addEventListener('dragstart', (e) => {
                const line = Number(badge.dataset.line);
                const segment = Number(badge.dataset.segment);
                draggedChord = lines[line]?.segments[segment]?.chord;
                dragSource = { type: 'canvas', line, segment };
                e.dataTransfer.setData('text/plain', draggedChord);
                e.dataTransfer.effectAllowed = 'move';
            });
            badge.addEventListener('dragend', () => {
                draggedChord = null;
                dragSource = null;
            });
        });

        canvas.querySelectorAll('[data-remove-chord]').forEach((btn) => {
            btn.addEventListener('mousedown', (e) => e.stopPropagation());
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const line = Number(btn.dataset.line);
                const segment = Number(btn.dataset.segment);
                lines[line].segments[segment].chord = null;
                renderAll();
            });
        });
    }

    function syncHiddenField() {
        contentField.value = serializeToChordPro(lines);
    }

    function renderAll() {
        renderPalette();
        renderCanvas();
    }

    function showImportStatus(message, isSuccess = true) {
        if (!importStatusEl) {
            return;
        }
        importStatusEl.textContent = message;
        importStatusEl.className = `text-xs ${isSuccess ? 'text-emerald-400' : 'text-slate-500'}`;
        window.setTimeout(() => {
            if (importStatusEl.textContent === message) {
                importStatusEl.textContent = '';
            }
        }, 4000);
    }

    function importChordSheet(text) {
        const parsed = parseChordSheetPaste(text);
        paletteChords.clear();

        lines = parsed.map((line) => {
            const segments = tokenizeLine(line.lyrics);
            applyChordsToSegments(segments, line.chords);
            line.chords.forEach((c) => paletteChords.add(c.name));
            return { segments };
        });

        if (!lines.length) {
            lines = [{ segments: [{ text: '', chord: null }] }];
        }

        lyricsSource.value = linesToLyricsText(lines);
        renderAll();
        showImportStatus(`Cifrado importado · ${paletteChords.size} acorde(s) detectado(s)`);
    }

    function tryImportChordSheet(text) {
        if (!detectChordSheetFormat(text)) {
            return false;
        }
        importChordSheet(text);
        return true;
    }

    function rebuildFromLyricsText(text) {
        const preserved = serializeToChordPro(lines);
        const oldParsed = preserved.split('\n').map(parseChordProLine);
        const newRawLines = text.replace(/\r\n/g, '\n').split('\n');

        lines = newRawLines.map((lyricLine, index) => {
            const segments = tokenizeLine(lyricLine);
            const old = oldParsed[index];
            if (old && old.lyrics === lyricLine) {
                applyChordsToSegments(segments, old.chords);
                old.chords.forEach((c) => paletteChords.add(c.name));
            }
            return { segments };
        });

        if (!lines.length) {
            lines = [{ segments: [{ text: '', chord: null }] }];
        }

        renderAll();
    }

    function openFloater() {
        if (!floater || !searchInput) {
            return;
        }
        floaterOpen = true;
        floater.classList.remove('hidden', 'opacity-0', 'pointer-events-none');
        floater.classList.add('opacity-100');
        searchInput.value = '';
        fetchSearchChords('');
        searchInput.focus();
    }

    function closeFloater() {
        if (!floater || !floaterOpen) {
            return;
        }
        floaterOpen = false;
        floater.classList.add('opacity-0', 'pointer-events-none');
        floater.classList.remove('opacity-100');
        window.setTimeout(() => {
            if (!floaterOpen) {
                floater.classList.add('hidden');
            }
        }, 150);
    }

    function renderSearchResults() {
        if (!resultsList) {
            return;
        }
        if (!searchChords.length) {
            resultsList.innerHTML = '<p class="px-3 py-3 text-sm text-slate-500">Sin acordes.</p>';
            return;
        }

        resultsList.innerHTML = searchChords
            .map((chord, index) => {
                const active = index === searchActiveIndex
                    ? 'bg-violet-600/20 border-violet-500/40 text-white'
                    : 'border-transparent text-slate-300 hover:bg-white/5';
                return `<button type="button" data-search-chord-index="${index}" class="flex w-full items-center justify-between rounded-lg border px-3 py-2 text-left text-sm transition ${active}">
                    <span class="font-mono font-semibold">${escapeHtml(chord.name)}</span>
                    <span class="text-xs text-slate-500">${escapeHtml(chord.root_note)}</span>
                </button>`;
            })
            .join('');

        resultsList.querySelectorAll('[data-search-chord-index]').forEach((btn) => {
            btn.addEventListener('click', () => {
                addChordToPalette(searchChords[Number(btn.dataset.searchChordIndex)]?.name);
                closeFloater();
            });
        });
    }

    async function fetchSearchChords(query) {
        if (!statusEl) {
            return;
        }
        statusEl.textContent = 'Buscando…';
        try {
            const url = new URL(searchUrl, window.location.origin);
            if (query) {
                url.searchParams.set('q', query);
            }
            const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                throw new Error('search failed');
            }
            searchChords = await response.json();
            searchActiveIndex = 0;
            renderSearchResults();
            statusEl.textContent = `${searchChords.length} acorde(s)`;
        } catch {
            searchChords = [];
            renderSearchResults();
            statusEl.textContent = 'Error al cargar';
        }
    }

    function addChordToPalette(name) {
        if (!name) {
            return;
        }
        paletteChords.add(name);
        renderPalette();
    }

    lyricsSource.addEventListener('paste', (event) => {
        const text = event.clipboardData?.getData('text') ?? '';
        if (tryImportChordSheet(text)) {
            event.preventDefault();
        }
    });

    lyricsSource.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const text = lyricsSource.value;
            if (tryImportChordSheet(text)) {
                return;
            }
            rebuildFromLyricsText(text);
        }, 300);
    });

    root.querySelector('[data-add-chord]')?.addEventListener('click', openFloater);

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchSearchChords(searchInput.value.trim()), 200);
        });
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeFloater();
                return;
            }
            if (!searchChords.length) {
                return;
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                searchActiveIndex = Math.min(searchActiveIndex + 1, searchChords.length - 1);
                renderSearchResults();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                searchActiveIndex = Math.max(searchActiveIndex - 1, 0);
                renderSearchResults();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                addChordToPalette(searchChords[searchActiveIndex]?.name);
                closeFloater();
            }
        });
    }

    document.addEventListener('mousedown', (e) => {
        if (!floaterOpen || !floater) {
            return;
        }
        if (!floater.contains(e.target) && !e.target.closest('[data-add-chord]')) {
            closeFloater();
        }
    });

    form?.addEventListener('submit', () => {
        syncHiddenField();
    });

    loadInitial();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-visual-chord-editor]').forEach(initVisualChordEditor);
});
