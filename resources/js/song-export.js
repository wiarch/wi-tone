import { paintGuitarSvg, paintKeyboardSvg } from './lib/chord-diagram-render.js';
import { chordProToBlocks } from './lib/chord-sheet-parser.js';
import { readSongContent, renderBlocksHtml } from './lib/chord-sheet-view.js';

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
    dotFill: 'var(--export-chord-color, #e85d04)',
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

function initSongExport() {
    const root = document.querySelector('[data-song-export]');
    if (!root) {
        return;
    }

    const blocks = chordProToBlocks(readSongContent(root));
    const library = JSON.parse(root.dataset.diagramLibrary || '{}');
    const chordNames = JSON.parse(root.dataset.chordNames || '[]');

    const sheetEl = root.querySelector('[data-export-sheet]');
    const diagramsEl = root.querySelector('[data-export-diagrams]');
    const diagramsSection = root.querySelector('[data-export-diagrams-section]');

    const state = {
        fontSize: 13,
        instrument: 'guitar',
        showChords: true,
        showLyrics: true,
        showDiagrams: true,
        accent: '#e85d04',
    };

    function applyAccent(color) {
        state.accent = color;
        root.style.setProperty('--export-chord-color', color);
        root.querySelectorAll('[data-accent-color]').forEach((btn) => {
            btn.classList.toggle('ring-2', btn.dataset.accentColor === color);
            btn.classList.toggle('ring-gray-400', btn.dataset.accentColor === color);
        });
        renderDiagrams();
    }

    function renderSheet() {
        if (!blocks.length) {
            sheetEl.innerHTML = '<p class="text-gray-500">Sin contenido para exportar.</p>';
            return;
        }
        sheetEl.style.fontSize = `${state.fontSize}px`;
        sheetEl.innerHTML = renderBlocksHtml(blocks, {
            showChords: state.showChords,
            showLyrics: state.showLyrics,
            lyricClass: 'text-gray-900',
        });
        sheetEl.classList.toggle('hidden', !state.showChords && !state.showLyrics);
    }

    function renderDiagrams() {
        if (!state.showDiagrams || !chordNames.length) {
            diagramsSection.classList.add('hidden');
            return;
        }
        diagramsSection.classList.remove('hidden');

        diagramsEl.innerHTML = chordNames.map((name) => `
            <div class="export-diagram-card flex flex-col items-center break-inside-avoid">
                <span class="mb-1 font-mono text-xs font-semibold text-gray-800">${escapeHtml(name)}</span>
                <svg data-export-diagram="${escapeHtml(name)}" width="80" height="${state.instrument === 'keyboard' ? 56 : 96}" class="border border-gray-100 rounded"></svg>
            </div>
        `).join('');

        chordNames.forEach((name) => {
            const svg = diagramsEl.querySelector(`[data-export-diagram="${CSS.escape(name)}"]`);
            if (!svg) {
                return;
            }
            const rep = getDiagramRep(library, name, state.instrument);
            if (state.instrument === 'guitar') {
                paintGuitarSvgForPrint(svg, rep || [-1, -1, -1, -1, -1, -1]);
            } else {
                paintKeyboardSvg(svg, rep || [], {
                    width: 80,
                    height: 56,
                    whiteW: 9,
                    whiteH: 40,
                    blackW: 5,
                    blackH: 24,
                    semitones: 14,
                    bgFill: '#ffffff',
                    inactiveFill: '#e5e7eb',
                    activeFill: state.accent,
                    activeFillDark: state.accent,
                });
            }
        });
    }

    function paintGuitarSvgForPrint(svgEl, representation) {
        paintGuitarSvg(svgEl, representation, {
            ...PRINT_GUITAR,
            dotFill: state.accent,
        });
    }

    function render() {
        renderSheet();
        renderDiagrams();
    }

    root.querySelector('[data-font-down]')?.addEventListener('click', () => {
        state.fontSize = Math.max(10, state.fontSize - 1);
        renderSheet();
    });
    root.querySelector('[data-font-up]')?.addEventListener('click', () => {
        state.fontSize = Math.min(20, state.fontSize + 1);
        renderSheet();
    });

    root.querySelectorAll('[data-accent-color]').forEach((btn) => {
        btn.addEventListener('click', () => applyAccent(btn.dataset.accentColor));
    });

    root.querySelector('[data-export-instrument]')?.addEventListener('change', (e) => {
        state.instrument = e.target.value;
        renderDiagrams();
    });

    root.querySelector('[data-toggle-chords]')?.addEventListener('change', (e) => {
        state.showChords = e.target.checked;
        renderSheet();
    });
    root.querySelector('[data-toggle-lyrics]')?.addEventListener('change', (e) => {
        state.showLyrics = e.target.checked;
        renderSheet();
    });
    root.querySelector('[data-toggle-diagrams]')?.addEventListener('change', (e) => {
        state.showDiagrams = e.target.checked;
        renderDiagrams();
    });

    root.querySelector('[data-print-btn]')?.addEventListener('click', () => window.print());

    applyAccent(state.accent);
    render();
}

document.addEventListener('DOMContentLoaded', initSongExport);
