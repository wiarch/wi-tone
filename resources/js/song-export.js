import { paintGuitarSvg, paintKeyboardSvg } from './lib/chord-diagram-render.js';

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

function buildChordLine(chords, lyricLength) {
    if (!chords.length) {
        return '';
    }
    const width = Math.max(lyricLength, ...chords.map(({ pos, name }) => pos + name.length));
    const chars = Array(width).fill(' ');
    for (const { pos, name } of chords) {
        for (let j = 0; j < name.length; j++) {
            if (pos + j < chars.length) {
                chars[pos + j] = name[j];
            }
        }
    }
    return chars.join('').replace(/\s+$/, '');
}

function isSectionHeader(line) {
    const t = line.lyrics.trim();
    return !line.chords?.length && /^\[[^\]]+\]$/.test(t);
}

function renderExportSheet(lines, { showChords, showLyrics }) {
    return lines.map((line) => {
        if (isSectionHeader(line)) {
            return `<div class="export-section break-inside-avoid font-sans text-sm font-semibold" style="color:var(--export-chord-color)">${escapeHtml(line.lyrics.trim())}</div>`;
        }

        const chords = line.chords || [];
        const lyrics = line.lyrics;
        const chordRow = showChords && chords.length
            ? `<div class="export-chord-row relative h-[1.2em] leading-none">${chords.map((c) => `<span class="absolute top-0 font-semibold" style="left:${c.pos}ch;color:var(--export-chord-color)">${escapeHtml(c.name)}</span>`).join('')}</div>`
            : '';

        const lyricRow = showLyrics
            ? `<div class="export-lyric-row whitespace-pre text-gray-900">${escapeHtml(lyrics) || '&nbsp;'}</div>`
            : '';

        if (!chordRow && !lyricRow) {
            return '';
        }

        return `<div class="export-line break-inside-avoid py-0.5">${chordRow}${lyricRow}</div>`;
    }).join('');
}

function getDiagramRep(library, name, instrument) {
    return library[name]?.[instrument]?.[0]?.representation ?? null;
}

function initSongExport() {
    const root = document.querySelector('[data-song-export]');
    if (!root) {
        return;
    }

    const lines = JSON.parse(root.dataset.lines || '[]');
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
        if (!lines.length) {
            sheetEl.innerHTML = '<p class="text-gray-500">Sin contenido para exportar.</p>';
            return;
        }
        sheetEl.style.fontSize = `${state.fontSize}px`;
        sheetEl.innerHTML = renderExportSheet(lines, state);
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
