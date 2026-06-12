import {
    CAROUSEL_GUITAR_LAYOUT,
    paintGuitarSvg,
    paintKeyboardSvg,
    transposeChord,
    transposeKey,
} from './lib/chord-diagram-render.js';
import { chordProToBlocks } from './lib/chord-sheet-parser.js';
import { readSongContent, renderBlocksHtml, transposeBlocks } from './lib/chord-sheet-view.js';

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function getDiagramData(library, chordName, instrument) {
    const entry = library[chordName];
    if (!entry) {
        return null;
    }
    return entry[instrument]?.[0]?.representation ?? null;
}

function initSongPerformance() {
    const root = document.querySelector('[data-song-performance]');
    if (!root) {
        return;
    }

    const baseBlocks = chordProToBlocks(readSongContent(root));
    const library = JSON.parse(root.dataset.diagramLibrary || '{}');
    const chordNames = JSON.parse(root.dataset.chordNames || '[]');
    const songKey = root.dataset.songKey || 'C';

    const sheetEl = root.querySelector('[data-chord-sheet]');
    const carouselEl = root.querySelector('[data-chord-carousel]');
    const carouselWrap = root.querySelector('[data-chord-carousel-wrap]');
    const displayKeyEl = root.querySelector('[data-display-key]');
    const transposeEl = root.querySelector('[data-transpose-value]');
    const textSizeEl = root.querySelector('[data-text-size-value]');
    const textSizeInput = root.querySelector('[data-text-size]');
    const metroBpm = root.querySelector('[data-metro-bpm]');
    const metroToggle = root.querySelector('[data-metro-toggle]');

    const state = {
        transpose: 0,
        viewMode: 'main',
        instrument: 'guitar',
        fontSize: 100,
        autoscroll: false,
        showDiagrams: true,
        capo: 0,
    };

    let scrollTimer = null;
    let metroTimer = null;
    let audioCtx = null;

    function render() {
        if (!baseBlocks.length) {
            return;
        }

        const blocks = transposeBlocks(baseBlocks, state.transpose);
        const showChords = state.viewMode !== 'lyrics';

        sheetEl.style.fontSize = `${state.fontSize}%`;
        sheetEl.innerHTML = `<div class="chord-sheet min-h-[200px] rounded-xl border border-dashed border-white/10 bg-[#0a0f1a] p-4 sm:p-5">${renderBlocksHtml(blocks, { showChords, originalBlocks: baseBlocks })}</div>`;
        displayKeyEl.textContent = transposeKey(songKey, state.transpose);
        transposeEl.textContent = state.transpose > 0 ? `+${state.transpose}` : String(state.transpose);
        textSizeEl.textContent = `${state.fontSize}%`;

        root.querySelectorAll('.chord-trigger').forEach((btn) => {
            btn.addEventListener('click', () => highlightCarousel(btn.dataset.chord));
        });

        renderCarousel();
    }

    function displayChordName(name) {
        return transposeChord(name, state.transpose);
    }

    function renderCarousel() {
        if (!carouselEl) {
            return;
        }

        carouselWrap.classList.toggle('hidden', !state.showDiagrams || state.viewMode === 'lyrics');

        carouselEl.innerHTML = chordNames.map((name) => {
            const label = displayChordName(name);
            return `<button type="button" data-carousel-chord="${escapeHtml(name)}" class="carousel-chord shrink-0 rounded-lg border border-white/10 bg-[#1a2236] p-2 text-center transition hover:border-amber-500/40 hover:bg-[#1f2a42]">
                <svg data-carousel-svg="${escapeHtml(name)}" width="72" height="88" class="mx-auto" aria-hidden="true"></svg>
                <span class="mt-1 block font-mono text-xs font-semibold text-amber-400">${escapeHtml(label)}</span>
            </button>`;
        }).join('');

        chordNames.forEach((name) => {
            const svg = carouselEl.querySelector(`[data-carousel-svg="${CSS.escape(name)}"]`);
            if (!svg) {
                return;
            }
            const rep = getDiagramData(library, name, state.instrument);
            if (state.instrument === 'guitar') {
                paintGuitarSvg(svg, rep || [-1, -1, -1, -1, -1, -1], CAROUSEL_GUITAR_LAYOUT);
            } else {
                paintKeyboardSvg(svg, rep || [], { width: 72, height: 88, whiteW: 8, whiteH: 50, blackW: 5, blackH: 30, semitones: 14, bgFill: '#1a2236' });
            }
        });

        carouselEl.querySelectorAll('[data-carousel-chord]').forEach((btn) => {
            btn.addEventListener('click', () => highlightCarousel(btn.dataset.carouselChord, true));
        });
    }

    function highlightCarousel(chordName, scrollTo) {
        carouselEl?.querySelectorAll('[data-carousel-chord]').forEach((btn) => {
            btn.classList.toggle('border-amber-500/60', btn.dataset.carouselChord === chordName);
            btn.classList.toggle('ring-1', btn.dataset.carouselChord === chordName);
            btn.classList.toggle('ring-amber-500/40', btn.dataset.carouselChord === chordName);
        });
        if (scrollTo && carouselEl) {
            const btn = carouselEl.querySelector(`[data-carousel-chord="${CSS.escape(chordName)}"]`);
            btn?.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
    }

    function setAutoscroll(on) {
        state.autoscroll = on;
        clearInterval(scrollTimer);
        root.querySelector('[data-autoscroll-btn]')?.classList.toggle('bg-amber-500/20', on);
        root.querySelector('[data-autoscroll-btn]')?.classList.toggle('text-amber-300', on);
        if (on) {
            scrollTimer = setInterval(() => {
                sheetEl.scrollTop += 1;
            }, 50);
        }
    }

    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            root.requestFullscreen?.();
            document.body.classList.add('song-stage-mode');
        } else {
            document.exitFullscreen?.();
            document.body.classList.remove('song-stage-mode');
        }
    }

    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) {
            document.body.classList.remove('song-stage-mode');
        }
    });

    root.querySelector('[data-autoscroll-btn]')?.addEventListener('click', () => setAutoscroll(!state.autoscroll));
    root.querySelector('[data-fullscreen-btn]')?.addEventListener('click', toggleFullscreen);

    root.querySelector('[data-transpose-down]')?.addEventListener('click', () => {
        state.transpose -= 1;
        render();
    });
    root.querySelector('[data-transpose-up]')?.addEventListener('click', () => {
        state.transpose += 1;
        render();
    });

    root.querySelector('[data-instrument]')?.addEventListener('change', (e) => {
        state.instrument = e.target.value;
        renderCarousel();
    });

    root.querySelector('[data-capo]')?.addEventListener('change', (e) => {
        state.capo = Number(e.target.value);
        root.querySelector('[data-capo-label]').textContent = state.capo ? `Traste ${state.capo}` : 'Sin capo';
    });

    textSizeInput?.addEventListener('input', () => {
        state.fontSize = Number(textSizeInput.value);
        sheetEl.style.fontSize = `${state.fontSize}%`;
        textSizeEl.textContent = `${state.fontSize}%`;
    });

    root.querySelector('[data-show-diagrams]')?.addEventListener('change', (e) => {
        state.showDiagrams = e.target.checked;
        renderCarousel();
    });

    root.querySelectorAll('[data-view-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            state.viewMode = tab.dataset.viewTab;
            root.querySelectorAll('[data-view-tab]').forEach((t) => {
                const active = t === tab;
                t.className = `rounded-md px-3 py-1.5 text-sm font-medium transition ${active ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-slate-200'}`;
            });
            render();
        });
    });

    metroToggle?.addEventListener('click', () => {
        if (metroTimer) {
            clearInterval(metroTimer);
            metroTimer = null;
            metroToggle.textContent = '▶';
            return;
        }
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        const bpm = Number(metroBpm?.value || 120);
        const spb = 60 / bpm;
        let next = audioCtx.currentTime + 0.05;
        metroToggle.textContent = '■';
        metroTimer = setInterval(() => {
            while (next < audioCtx.currentTime + 0.1) {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.frequency.value = 1000;
                gain.gain.setValueAtTime(0.0001, next);
                gain.gain.exponentialRampToValueAtTime(0.3, next + 0.002);
                gain.gain.exponentialRampToValueAtTime(0.0001, next + 0.04);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(next);
                osc.stop(next + 0.05);
                next += spb;
            }
        }, 25);
    });

    render();
}

document.addEventListener('DOMContentLoaded', initSongPerformance);
