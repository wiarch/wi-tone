/**
 * Editor visual de cifrado — vista alineada estilo Ultimate Guitar.
 */

import {
    blocksToChordPro,
    blocksToLyricsText,
    buildChordLineFromChords,
    chordProToBlocks,
    collectChordsFromBlocks,
    detectChordSheetFormat,
    extractChordsFromLine,
    parseChordSheetBlocks,
    rebuildChordLine,
} from './lib/chord-sheet-parser.js';
import { renderChordRowHtml, transposeChordLine } from './lib/chord-sheet-view.js';
import { noteIndex, transposeKey } from './lib/chord-diagram-render.js';

const KEY_GRID = ['A', 'Bb', 'B', 'C', 'Db', 'D', 'Eb', 'E', 'F', 'F#', 'G', 'Ab'];

function deepCloneBlocks(source) {
    return source.map((b) => ({ ...b }));
}

function transposeBlocks(source, semitones) {
    if (!semitones) {
        return deepCloneBlocks(source);
    }

    return source.map((block) => {
        if (block.type === 'pair') {
            return {
                ...block,
                chordLine: transposeChordLine(block.chordLine, semitones),
            };
        }
        if (block.type === 'chords') {
            return {
                ...block,
                chordLine: transposeChordLine(block.chordLine, semitones),
            };
        }
        return { ...block };
    });
}

function parseKeyRoot(key) {
    const match = String(key).trim().match(/^([A-G][#b♯♭]?)/i);
    if (!match) {
        return null;
    }

    let root = match[1].replace('♯', '#').replace('♭', 'b');
    root = root.charAt(0).toUpperCase() + root.slice(1);
    const flatMap = { DB: 'Db', EB: 'Eb', GB: 'Gb', AB: 'Ab', BB: 'Bb' };
    return flatMap[root.toUpperCase()] ?? root;
}

function semitonesBetweenKeys(fromKey, toKey) {
    const from = parseKeyRoot(fromKey);
    const to = parseKeyRoot(toKey);
    if (!from || !to) {
        return 0;
    }

    const fromIdx = noteIndex(from);
    const toIdx = noteIndex(to);
    if (fromIdx < 0 || toIdx < 0) {
        return 0;
    }

    let diff = toIdx - fromIdx;
    if (diff > 6) {
        diff -= 12;
    } else if (diff < -6) {
        diff += 12;
    }

    return diff;
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
    const transposeDisplayKey = root.querySelector('[data-transpose-display-key]');
    const form = root.closest('form');
    const keyField = form?.querySelector('[data-key-field]');

    if (!lyricsSource || !canvas || !paletteEl || !contentField) {
        return;
    }

    let blocks = [];
    let originalBlocks = null;
    let originalKey = '';
    let transposeSemitones = 0;
    let searchChords = [];
    let searchActiveIndex = 0;
    let debounceTimer = null;
    let floaterOpen = false;
    let suppressLyricsRebuild = false;
    let chordDrag = null;
    let charWidthCache = null;
    let renderTimer = null;

    const paletteChords = new Set();

    function linesFromText(text) {
        const normalized = String(text ?? '').replace(/\r\n/g, '\n');
        if (normalized === '') {
            return [''];
        }
        return normalized.split('\n');
    }

    function getCharWidth(sampleEl) {
        if (charWidthCache) {
            return charWidthCache;
        }
        const probe = document.createElement('span');
        probe.className = sampleEl?.className ?? 'font-mono text-sm';
        probe.style.visibility = 'hidden';
        probe.style.position = 'absolute';
        probe.textContent = 'M';
        (sampleEl ?? canvas).appendChild(probe);
        charWidthCache = probe.getBoundingClientRect().width || 8;
        probe.remove();
        return charWidthCache;
    }

    function charIndexInRow(rowEl, clientX) {
        const rect = rowEl.getBoundingClientRect();
        const charW = getCharWidth(rowEl);
        return Math.max(0, Math.round((clientX - rect.left) / charW));
    }

    function chordsFromBlock(block) {
        return extractChordsFromLine(block.chordLine);
    }

    function applyChordsToBlock(block, chords) {
        const minLen =
            block.type === 'pair'
                ? Math.max(block.chordLine.length, block.lyricLine.length)
                : block.chordLine.length;
        block.chordLine = buildChordLineFromChords(chords, minLen);
    }

    function resolveChordOverlaps(chords) {
        const sorted = [...chords].sort((a, b) => a.pos - b.pos);
        for (let i = 1; i < sorted.length; i++) {
            const prev = sorted[i - 1];
            const minPos = prev.pos + prev.name.length + 1;
            if (sorted[i].pos < minPos) {
                sorted[i].pos = minPos;
            }
        }
        return sorted;
    }

    function syncLyricsTextarea() {
        suppressLyricsRebuild = true;
        lyricsSource.value = blocksToLyricsText(blocks);
        suppressLyricsRebuild = false;
    }

    function afterBlocksChange({ syncTextarea = true } = {}) {
        if (transposeSemitones === 0) {
            originalBlocks = deepCloneBlocks(blocks);
        }
        if (syncTextarea) {
            syncLyricsTextarea();
        }
        syncPalette();
        renderAll();
    }

    function scheduleLyricsRebuild() {
        clearTimeout(renderTimer);
        renderTimer = setTimeout(() => {
            rebuildLyricsFromText(lyricsSource.value);
        }, 40);
    }

    function chordRowHtml(blockIndex, chordLine, minWidth = 0) {
        return renderChordRowHtml(chordLine, { minWidth, blockIndex, interactive: true });
    }

    function syncPalette() {
        paletteChords.clear();
        collectChordsFromBlocks(blocks).forEach((name) => paletteChords.add(name));
    }

    function captureBaseline() {
        originalBlocks = deepCloneBlocks(blocks);
        originalKey = keyField?.value.trim() ?? '';
        transposeSemitones = 0;
        updateTransposeUI();
    }

    function applyTransposeOffset(semitones) {
        if (!originalBlocks) {
            captureBaseline();
        }

        transposeSemitones = semitones;
        blocks = transposeBlocks(originalBlocks, transposeSemitones);

        if (keyField && originalKey) {
            keyField.value = transposeKey(originalKey, transposeSemitones);
        }

        syncPalette();
        updateTransposeUI();
        renderAll();
    }

    function updateTransposeUI() {
        const currentKey = keyField?.value.trim() || '—';
        if (transposeDisplayKey) {
            transposeDisplayKey.textContent = currentKey;
        }

        const activeRoot = parseKeyRoot(currentKey);
        root.querySelectorAll('[data-transpose-key]').forEach((btn) => {
            const isActive = btn.dataset.transposeKey === activeRoot;
            btn.classList.toggle('border-amber-500/60', isActive);
            btn.classList.toggle('bg-white', isActive);
            btn.classList.toggle('text-slate-900', isActive);
            btn.classList.toggle('border-white/10', !isActive);
            btn.classList.toggle('text-slate-400', !isActive);
        });
    }

    function loadInitial() {
        const initial = initialEl?.value ?? '';
        blocks = chordProToBlocks(initial);
        syncPalette();
        originalBlocks = null;
        originalKey = keyField?.value.trim() ?? '';
        transposeSemitones = 0;

        suppressLyricsRebuild = true;
        lyricsSource.value = blocks.length ? blocksToLyricsText(blocks) : '';
        suppressLyricsRebuild = false;

        if (!blocks.length) {
            blocks = [{ type: 'pair', chordLine: '', lyricLine: '' }];
        }

        if (blocks.length) {
            captureBaseline();
        }

        if (keyField && keyField.value) {
            keyField.dataset.initialKey = keyField.value;
        }

        renderAll();
        updateTransposeUI();
    }

    function renderPalette() {
        const items = [...paletteChords].sort((a, b) => a.localeCompare(b));

        if (!items.length) {
            paletteEl.innerHTML = '<p class="text-xs text-slate-500">Busca acordes arriba o arrástralos a una línea ámbar.</p>';
            return;
        }

        paletteEl.innerHTML = items
            .map(
                (name) => `<span
                    draggable="true"
                    data-palette-chord="${escapeHtml(name)}"
                    class="chord-palette-item inline-flex cursor-grab select-none items-center rounded-lg border border-violet-500/30 bg-violet-600/20 px-3 py-1.5 font-mono text-sm font-semibold text-violet-200 hover:bg-violet-600/35 active:cursor-grabbing"
                >${escapeHtml(name)}</span>`
            )
            .join('');

        paletteEl.querySelectorAll('[data-palette-chord]').forEach((el) => {
            el.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', el.dataset.paletteChord);
                e.dataTransfer.effectAllowed = 'copy';
            });
        });
    }

    function bindChordRow(rowEl, blockIndex) {
        rowEl.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        });

        rowEl.addEventListener('drop', (e) => {
            e.preventDefault();
            const name = e.dataTransfer.getData('text/plain');
            if (!name) {
                return;
            }
            const block = blocks[blockIndex];
            const chords = chordsFromBlock(block);
            const pos = charIndexInRow(rowEl, e.clientX);
            chords.push({ pos, name });
            applyChordsToBlock(block, resolveChordOverlaps(chords));
            paletteChords.add(name);
            if (transposeSemitones === 0) {
                originalBlocks = deepCloneBlocks(blocks);
            }
            updateBlockDom(blockIndex);
            syncHiddenField();
            renderPalette();
        });

        rowEl.querySelectorAll('[data-sheet-chord]').forEach((badge) => {
            badge.addEventListener('pointerdown', (e) => {
                if (e.button !== 0 || e.target.closest('[data-remove-sheet-chord]')) {
                    return;
                }
                e.preventDefault();
                const chordIdx = Number(badge.dataset.chordIdx);
                const chords = chordsFromBlock(blocks[blockIndex]);
                chordDrag = {
                    blockIndex,
                    chordIdx,
                    startPos: chords[chordIdx]?.pos ?? 0,
                    currentPos: chords[chordIdx]?.pos ?? 0,
                    badge,
                };
                document.body.classList.add('cursor-grabbing');
                document.addEventListener('pointermove', onChordDragMove);
                document.addEventListener('pointerup', onChordDragEnd);
                document.addEventListener('pointercancel', onChordDragEnd);
            });
        });

        rowEl.querySelectorAll('[data-remove-sheet-chord]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const ci = Number(btn.dataset.chordIdx);
                const block = blocks[blockIndex];
                const chords = chordsFromBlock(block);
                chords.splice(ci, 1);
                applyChordsToBlock(block, chords);
                updateBlockDom(blockIndex);
                syncHiddenField();
                syncPalette();
                renderPalette();
            });
        });
    }

    function onChordDragMove(e) {
        if (!chordDrag) {
            return;
        }
        const row = canvas.querySelector(`[data-chord-row="${chordDrag.blockIndex}"]`);
        if (!row) {
            return;
        }
        chordDrag.currentPos = charIndexInRow(row, e.clientX);
        chordDrag.badge.style.left = `${chordDrag.currentPos}ch`;
    }

    function onChordDragEnd() {
        if (!chordDrag) {
            return;
        }

        const { blockIndex, chordIdx, currentPos } = chordDrag;
        const block = blocks[blockIndex];
        const chords = chordsFromBlock(block);
        if (chords[chordIdx]) {
            chords[chordIdx].pos = currentPos;
            applyChordsToBlock(block, resolveChordOverlaps(chords));
        }

        chordDrag = null;
        document.body.classList.remove('cursor-grabbing');
        document.removeEventListener('pointermove', onChordDragMove);
        document.removeEventListener('pointerup', onChordDragEnd);
        document.removeEventListener('pointercancel', onChordDragEnd);

        if (transposeSemitones === 0) {
            originalBlocks = deepCloneBlocks(blocks);
        }

        updateBlockDom(blockIndex);
        syncHiddenField();
    }

    function updateBlockDom(blockIndex) {
        const block = blocks[blockIndex];
        const wrap = canvas.querySelector(`[data-sheet-block="${blockIndex}"]`);
        if (!block || !wrap) {
            renderCanvas();
            return;
        }

        const chordSlot = wrap.querySelector('[data-chord-slot]');
        const minW = block.type === 'pair' ? block.lyricLine.length : 0;
        if (chordSlot) {
            chordSlot.innerHTML = chordRowHtml(blockIndex, block.chordLine, minW);
            const row = chordSlot.querySelector('[data-chord-row]');
            if (row) {
                bindChordRow(row, blockIndex);
            }
        }
    }

    function bindCanvasInteractions() {
        charWidthCache = null;

        canvas.querySelectorAll('[data-chord-row]').forEach((row) => {
            bindChordRow(row, Number(row.dataset.chordRow));
        });
    }

    function renderCanvas() {
        const pairBlocks = blocks.filter((b) => b.type === 'pair');

        if (!pairBlocks.length) {
            canvas.innerHTML = '<p class="font-mono text-sm text-slate-500">Escribe la letra a la izquierda. Aquí verás cada línea con sus acordes.</p>';
            syncHiddenField();
            return;
        }

        canvas.innerHTML = blocks
            .map((block, blockIndex) => {
                if (block.type === 'pair') {
                    const lyricHtml = block.lyricLine
                        ? escapeHtml(block.lyricLine)
                        : '<span class="text-slate-600 italic">— línea vacía —</span>';

                    return `<div data-sheet-block="${blockIndex}" class="sheet-pair mb-5 rounded-lg border border-transparent p-1">
                        <div data-chord-slot>${chordRowHtml(blockIndex, block.chordLine, Math.max(block.lyricLine.length, 1))}</div>
                        <div
                            data-lyric-row="${blockIndex}"
                            class="mt-1 min-h-[1.25rem] whitespace-pre-wrap font-mono text-sm leading-relaxed text-slate-200"
                        >${lyricHtml}</div>
                    </div>`;
                }

                if (block.type === 'chords') {
                    return `<div data-sheet-block="${blockIndex}" class="sheet-chords mb-4 rounded-lg border border-transparent p-1">
                        <div data-chord-slot>${chordRowHtml(blockIndex, block.chordLine)}</div>
                    </div>`;
                }

                if (block.type === 'lyrics') {
                    return `<div data-sheet-block="${blockIndex}" class="sheet-lyrics mb-4 rounded-lg border border-transparent p-1">
                        <div data-lyric-row="${blockIndex}" class="min-h-[1.25rem] whitespace-pre-wrap font-mono text-sm leading-relaxed text-slate-200">${escapeHtml(block.lyricLine)}</div>
                    </div>`;
                }

                return '';
            })
            .join('');

        bindCanvasInteractions();
        syncHiddenField();
    }

    function syncHiddenField() {
        contentField.value = blocks.length ? blocksToChordPro(blocks) : '';
    }

    function renderAll() {
        renderPalette();
        renderCanvas();
    }

    function showImportStatus(message) {
        if (!importStatusEl) {
            return;
        }
        importStatusEl.textContent = message;
        importStatusEl.className = 'text-xs text-emerald-400';
        window.setTimeout(() => {
            if (importStatusEl.textContent === message) {
                importStatusEl.textContent = '';
            }
        }, 4000);
    }

    function importChordSheet(text) {
        blocks = parseChordSheetBlocks(text);

        if (!blocks.length) {
            showImportStatus('No se detectaron pares acordes/letra');
            return;
        }

        syncPalette();

        if (!keyField?.value.trim()) {
            const first = [...paletteChords][0];
            const root = first ? parseKeyRoot(first) : null;
            if (root && keyField) {
                keyField.value = root;
            }
        }

        suppressLyricsRebuild = true;
        lyricsSource.value = blocksToLyricsText(blocks);
        suppressLyricsRebuild = false;

        captureBaseline();

        if (keyField?.value) {
            keyField.dataset.initialKey = keyField.value;
        }

        renderAll();
        showImportStatus(`Cifrado importado · ${paletteChords.size} acorde(s)`);
    }

    function tryImportChordSheet(text, { force = false } = {}) {
        if (!force && !detectChordSheetFormat(text)) {
            return false;
        }
        importChordSheet(text);
        return true;
    }

    function rebuildLyricsFromText(text) {
        const newLines = linesFromText(text);
        const existingPairs = blocks.filter((b) => b.type === 'pair');
        const nonPairBlocks = blocks.filter((b) => b.type !== 'pair');

        const newPairs = newLines.map((line, index) => ({
            type: 'pair',
            chordLine: existingPairs[index]?.chordLine ?? '',
            lyricLine: line,
        }));

        blocks = [...newPairs, ...nonPairBlocks];

        if (!originalBlocks && blocks.some((b) => b.type === 'pair' && (b.lyricLine || b.chordLine))) {
            captureBaseline();
        }

        if (transposeSemitones && originalBlocks) {
            originalBlocks = deepCloneBlocks(blocks);
            blocks = transposeBlocks(originalBlocks, transposeSemitones);
        }

        syncPalette();
        renderPalette();
        renderCanvas();
        syncHiddenField();
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
                const name = searchChords[Number(btn.dataset.searchChordIndex)]?.name;
                if (name) {
                    paletteChords.add(name);
                    renderPalette();
                    showImportStatus(`Acorde ${name} listo — arrástralo a la línea ámbar`);
                }
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

    lyricsSource.addEventListener('paste', (event) => {
        const text = event.clipboardData?.getData('text') ?? '';
        if (tryImportChordSheet(text, { force: true })) {
            event.preventDefault();
        }
    });

    lyricsSource.addEventListener('input', () => {
        if (suppressLyricsRebuild) {
            return;
        }
        scheduleLyricsRebuild();
    });

    root.querySelector('[data-add-chord]')?.addEventListener('click', openFloater);

    root.querySelector('[data-transpose-half-down]')?.addEventListener('click', () => {
        applyTransposeOffset(transposeSemitones - 1);
    });

    root.querySelector('[data-transpose-half-up]')?.addEventListener('click', () => {
        applyTransposeOffset(transposeSemitones + 1);
    });

    root.querySelector('[data-transpose-reset]')?.addEventListener('click', () => {
        applyTransposeOffset(0);
        if (keyField && originalKey) {
            keyField.value = originalKey;
        }
        updateTransposeUI();
    });

    root.querySelectorAll('[data-transpose-key]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetKey = btn.dataset.transposeKey;
            const base = originalKey || keyField?.value.trim() || targetKey;
            if (!originalBlocks) {
                captureBaseline();
            }
            if (!originalKey && keyField) {
                originalKey = base;
            }
            applyTransposeOffset(semitonesBetweenKeys(base, targetKey));
        });
    });

    keyField?.addEventListener('change', () => {
        const newKey = keyField.value.trim();
        if (!newKey) {
            return;
        }

        if (!originalBlocks?.length && blocks.length) {
            captureBaseline();
        }

        if (!originalBlocks?.length) {
            originalKey = newKey;
            updateTransposeUI();
            return;
        }

        const base = originalKey || keyField.dataset.initialKey || newKey;
        if (!originalKey) {
            originalKey = base;
        }

        applyTransposeOffset(semitonesBetweenKeys(originalKey, newKey));
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchSearchChords(searchInput.value.trim()), 200);
        });
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                e.preventDefault();
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
