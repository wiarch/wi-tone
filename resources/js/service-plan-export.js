import { paintGuitarSvg, paintKeyboardSvg, semitonesBetweenKeys } from './lib/chord-diagram-render.js';
import { chordProToBlocks } from './lib/chord-sheet-parser.js';
import { renderBlocksHtml, transposeBlocks } from './lib/chord-sheet-view.js';

const PRINT_GUITAR = {
    nutX: 16,
    fretW: 10,
    topY: 14,
    stringGap: 12,
    frets: 5,
    width: 80,
    height: 96,
    dotR: 4,
    bgFill: '#ffffff',
    dotFill: 'var(--plan-chord-color, #2563eb)',
};

const DEFAULTS = {
    fontSize: 14,
    lyricColor: '#111827',
    chordColor: '#2563eb',
    paper: 'a4',
    viewMode: 'export',
    showChords: true,
    showLyrics: true,
    showIndex: true,
    showDiagrams: false,
    transpose: 0,
};

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function getDiagramRep(library, name, instrument) {
    return library[name]?.[instrument]?.[0]?.representation ?? null;
}

function initServicePlanExport() {
    const root = document.querySelector('[data-plan-export]');
    if (!root) {
        return;
    }

    const entries = JSON.parse(root.querySelector('[data-plan-entries]')?.textContent || '[]');
    const library = JSON.parse(root.dataset.diagramLibrary || '{}');
    const planMeta = JSON.parse(root.querySelector('[data-plan-meta]')?.textContent || '{}');

    const songsEl = root.querySelector('[data-plan-songs]');
    const indexEl = root.querySelector('[data-plan-index]');
    const diagramsEl = root.querySelector('[data-plan-diagrams]');
    const diagramsSection = root.querySelector('[data-plan-diagrams-section]');

    const pageMode = root.dataset.pageMode || 'export';
    const savedSettings = JSON.parse(root.querySelector('[data-share-settings]')?.textContent || 'null') || {};

    const state = {
        ...DEFAULTS,
        ...savedSettings,
        viewMode: pageMode === 'public' ? 'share' : pageMode,
    };

    function getSettingsSnapshot() {
        return {
            fontSize: state.fontSize,
            lyricColor: state.lyricColor,
            chordColor: state.chordColor,
            paper: state.paper,
            showChords: state.showChords,
            showLyrics: state.showLyrics,
            showIndex: state.showIndex,
            showDiagrams: state.showDiagrams,
            transpose: state.transpose,
        };
    }

    function syncFormFromState() {
        const chordsToggle = root.querySelector('[data-toggle-chords]');
        const lyricsToggle = root.querySelector('[data-toggle-lyrics]');
        const indexToggle = root.querySelector('[data-toggle-index]');
        const diagramsToggle = root.querySelector('[data-toggle-diagrams]');
        const paperSelect = root.querySelector('[data-paper-size]');

        if (chordsToggle) chordsToggle.checked = state.showChords;
        if (lyricsToggle) lyricsToggle.checked = state.showLyrics;
        if (indexToggle) indexToggle.checked = state.showIndex;
        if (diagramsToggle) diagramsToggle.checked = state.showDiagrams;
        if (paperSelect) paperSelect.value = state.paper;
    }

    function applyStyles() {
        root.style.setProperty('--plan-lyric-color', state.lyricColor);
        root.style.setProperty('--plan-chord-color', state.chordColor);
        root.style.setProperty('--plan-title-color', state.lyricColor);
        root.dataset.paper = state.paper;

        root.querySelectorAll('[data-lyric-color]').forEach((btn) => {
            const active = btn.dataset.lyricColor === state.lyricColor;
            btn.classList.toggle('ring-2', active);
            btn.classList.toggle('ring-gray-400', active);
        });

        root.querySelectorAll('[data-chord-color]').forEach((btn) => {
            const active = btn.dataset.chordColor === state.chordColor;
            btn.classList.toggle('ring-2', active);
            btn.classList.toggle('ring-gray-400', active);
        });

        const fontLabel = root.querySelector('[data-font-size-label]');
        if (fontLabel) {
            fontLabel.textContent = `${state.fontSize}px`;
        }

        const transposeLabel = root.querySelector('[data-transpose-label]');
        if (transposeLabel) {
            transposeLabel.textContent = state.transpose > 0 ? `+${state.transpose}` : String(state.transpose);
        }
    }

    function renderSongSheet(entry) {
        if (!entry.content?.trim()) {
            return '<p class="text-sm text-gray-400">Sin cifrado registrado para esta canción.</p>';
        }

        const baseBlocks = chordProToBlocks(entry.content);
        const baseSemitones = semitonesBetweenKeys(entry.original_key, entry.key);
        const blocks = transposeBlocks(baseBlocks, baseSemitones + state.transpose);

        return `<div class="plan-export-sheet font-mono leading-relaxed" style="font-size:${state.fontSize}px">${renderBlocksHtml(blocks, {
            showChords: state.showChords,
            showLyrics: state.showLyrics,
            lyricClass: 'plan-export-lyric',
            chordClass: 'plan-export-chord',
        })}</div>`;
    }

    function renderIndex() {
        if (!indexEl) {
            return;
        }

        const songEntries = entries.filter((e) => e.type !== 'section');

        if (!state.showIndex || !songEntries.length) {
            indexEl.classList.add('hidden');
            return;
        }

        indexEl.classList.remove('hidden');
        indexEl.innerHTML = `
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-300 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="py-2 pr-3 w-10">#</th>
                        <th class="py-2 pr-3">Categoría</th>
                        <th class="py-2 pr-3">Canción</th>
                        <th class="py-2 pr-3">Artista</th>
                        <th class="py-2 pr-3 w-16">Tono</th>
                        <th class="py-2">Asignado</th>
                    </tr>
                </thead>
                <tbody>
                    ${songEntries.map((entry) => `
                        <tr class="border-b border-gray-100">
                            <td class="py-2 pr-3 font-semibold">${entry.order}</td>
                            <td class="py-2 pr-3 text-gray-600">${escapeHtml(entry.category || '—')}</td>
                            <td class="py-2 pr-3 font-medium">${escapeHtml(entry.title)}</td>
                            <td class="py-2 pr-3 plan-export-artist">${escapeHtml(entry.artist)}</td>
                            <td class="py-2 pr-3 font-mono font-semibold plan-export-chord">${escapeHtml(entry.key || '—')}</td>
                            <td class="py-2 text-gray-600">${escapeHtml(entry.assigned || '—')}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    function renderSongs() {
        if (!songsEl) {
            return;
        }

        const hasContent = entries.some((e) => e.type === 'section' || e.type === 'song');

        if (!hasContent) {
            songsEl.innerHTML = '<p class="text-sm text-gray-500">Sin canciones en el plan.</p>';
            return;
        }

        let songNumber = 0;

        songsEl.innerHTML = entries
            .map((entry) => {
                if (entry.type === 'section') {
                    return `<div class="plan-section-heading mb-6 mt-8 border-b-2 border-gray-300 pb-2">
                        <h2 class="text-xl font-bold uppercase tracking-wide text-gray-800">${escapeHtml(entry.section_title)}</h2>
                    </div>`;
                }

                songNumber += 1;
                const num = songNumber;

                if (state.viewMode === 'share') {
                    return `<details class="plan-share-item mb-3 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <summary class="flex cursor-pointer list-none items-center gap-3 px-4 py-3 hover:bg-gray-50 [&::-webkit-details-marker]:hidden">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-700">${num}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold plan-export-title">${escapeHtml(entry.title)}</p>
                                <p class="truncate text-sm plan-export-artist">${escapeHtml(entry.artist)} · <span class="font-mono plan-export-chord">${escapeHtml(entry.key || '—')}</span>${entry.category ? ` · ${escapeHtml(entry.category)}` : ''}</p>
                            </div>
                            <span class="text-xs text-gray-400">Ver cifrado</span>
                        </summary>
                        <div class="border-t border-gray-100 px-4 py-4">${renderSongSheet(entry)}</div>
                    </details>`;
                }

                return `<section class="plan-print-song mb-12 break-inside-avoid">
                    <header class="mb-4 border-b border-gray-200 pb-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">#${num}${entry.category ? ` · ${escapeHtml(entry.category)}` : ''}</p>
                        <h2 class="mt-1 text-2xl font-bold plan-export-title">${escapeHtml(entry.title)}</h2>
                        <p class="text-lg font-semibold plan-export-artist">${escapeHtml(entry.artist)}</p>
                        <p class="mt-1 text-sm text-gray-600">
                            Tono: <span class="font-mono font-semibold plan-export-chord">${escapeHtml(entry.key || '—')}</span>
                            ${entry.assigned ? ` · ${escapeHtml(entry.assigned)}` : ''}
                        </p>
                    </header>
                    ${renderSongSheet(entry)}
                </section>`;
            })
            .join('');
    }

    function renderDiagrams() {
        if (!diagramsSection || !diagramsEl) {
            return;
        }

        const names = [...new Set(entries.filter((e) => e.type !== 'section').flatMap((e) => e.chord_names || []))];

        if (!state.showDiagrams || !names.length) {
            diagramsSection.classList.add('hidden');
            return;
        }

        diagramsSection.classList.remove('hidden');
        diagramsEl.innerHTML = names.map((name) => `
            <div class="flex flex-col items-center break-inside-avoid">
                <span class="mb-1 font-mono text-xs font-semibold plan-export-chord">${escapeHtml(name)}</span>
                <svg data-plan-diagram="${escapeHtml(name)}" width="80" height="96" class="rounded border border-gray-100"></svg>
            </div>
        `).join('');

        names.forEach((name) => {
            const svg = diagramsEl.querySelector(`[data-plan-diagram="${CSS.escape(name)}"]`);
            if (!svg) {
                return;
            }
            const rep = getDiagramRep(library, name, 'guitar');
            paintGuitarSvg(svg, rep || [-1, -1, -1, -1, -1, -1], {
                ...PRINT_GUITAR,
                dotFill: state.chordColor,
            });
        });
    }

    function render() {
        applyStyles();
        renderIndex();
        renderSongs();
        renderDiagrams();
    }

    function resetSettings() {
        Object.assign(state, { ...DEFAULTS, viewMode: state.viewMode });
        syncFormFromState();
        render();
    }

    root.querySelector('[data-font-down]')?.addEventListener('click', () => {
        state.fontSize = Math.max(10, state.fontSize - 1);
        render();
    });
    root.querySelector('[data-font-up]')?.addEventListener('click', () => {
        state.fontSize = Math.min(22, state.fontSize + 1);
        render();
    });

    root.querySelector('[data-transpose-down]')?.addEventListener('click', () => {
        state.transpose -= 1;
        render();
    });
    root.querySelector('[data-transpose-up]')?.addEventListener('click', () => {
        state.transpose += 1;
        render();
    });

    root.querySelectorAll('[data-lyric-color]').forEach((btn) => {
        btn.addEventListener('click', () => {
            state.lyricColor = btn.dataset.lyricColor;
            render();
        });
    });

    root.querySelectorAll('[data-chord-color]').forEach((btn) => {
        btn.addEventListener('click', () => {
            state.chordColor = btn.dataset.chordColor;
            render();
        });
    });

    root.querySelector('[data-paper-size]')?.addEventListener('change', (e) => {
        state.paper = e.target.value;
        applyStyles();
    });

    root.querySelector('[data-toggle-chords]')?.addEventListener('change', (e) => {
        state.showChords = e.target.checked;
        render();
    });
    root.querySelector('[data-toggle-lyrics]')?.addEventListener('change', (e) => {
        state.showLyrics = e.target.checked;
        render();
    });
    root.querySelector('[data-toggle-index]')?.addEventListener('change', (e) => {
        state.showIndex = e.target.checked;
        render();
    });
    root.querySelector('[data-toggle-diagrams]')?.addEventListener('change', (e) => {
        state.showDiagrams = e.target.checked;
        render();
    });

    root.querySelector('[data-print-btn]')?.addEventListener('click', () => window.print());
    root.querySelector('[data-reset-btn]')?.addEventListener('click', resetSettings);

    root.querySelector('[data-publish-form]')?.addEventListener('submit', () => {
        const input = root.querySelector('[data-share-settings-input]');
        if (input) {
            input.value = JSON.stringify(getSettingsSnapshot());
        }
    });

    root.querySelector('[data-copy-url]')?.addEventListener('click', async () => {
        const input = root.querySelector('[data-public-url]');
        if (!input?.value) {
            return;
        }
        try {
            await navigator.clipboard.writeText(input.value);
        } catch {
            input.select();
            document.execCommand('copy');
        }
    });

    syncFormFromState();
    render();
}

document.addEventListener('DOMContentLoaded', initServicePlanExport);
