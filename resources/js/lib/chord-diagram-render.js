export const NOTE_NAMES = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
export const STRING_LABELS = ['E', 'A', 'D', 'G', 'B', 'e'];

export function noteIndex(name) {
    const sharp = NOTE_NAMES.indexOf(name);
    if (sharp !== -1) {
        return sharp;
    }
    const flatMap = { Db: 1, Eb: 3, Gb: 6, Ab: 8, Bb: 10 };
    return flatMap[name] ?? -1;
}

export function normalizeNoteName(note) {
    const match = String(note).trim().match(/^([A-Ga-g])([#b♯♭]?)(\d)?$/);
    if (!match) {
        return null;
    }
    let acc = (match[2] || '').replace('♯', '#').replace('♭', 'b');
    const letter = match[1].toUpperCase();
    const name = letter + acc;
    const flatMap = { DB: 'Db', EB: 'Eb', GB: 'Gb', AB: 'Ab', BB: 'Bb', CB: 'B', FB: 'E' };
    return flatMap[name] ?? name;
}

export function transposeChord(chord, semitones) {
    if (!semitones) {
        return chord;
    }
    const match = String(chord).trim().match(/^([A-G][#b♯♭]?)(.*)$/i);
    if (!match) {
        return chord;
    }
    const root = normalizeNoteName(match[1]);
    const idx = noteIndex(root);
    if (idx < 0) {
        return chord;
    }
    const newRoot = NOTE_NAMES[((idx + semitones) % 12 + 12) % 12];
    return newRoot + match[2];
}

export function transposeKey(key, semitones) {
    return transposeChord(key, semitones);
}

function pitchClassFromNote(note) {
    const normalized = normalizeNoteName(note);
    if (!normalized) {
        return null;
    }
    const idx = noteIndex(normalized);
    return idx < 0 ? null : idx;
}

function parseGuitarFrets(representation) {
    return (Array.isArray(representation) ? representation : []).map((f) => {
        if (f === 'x' || f === 'X') {
            return -1;
        }
        const n = Number(f);
        return Number.isNaN(n) ? -1 : n;
    });
}

function getDiagramView(numericFrets, layout) {
    const positive = numericFrets.filter((f) => f > 0);
    if (!positive.length) {
        return { useNut: true, baseFret: 0 };
    }

    const min = Math.min(...positive);
    const max = Math.max(...positive);

    if (max <= layout.frets) {
        return { useNut: true, baseFret: 0 };
    }

    return { useNut: false, baseFret: min };
}

function fretCenterX(fret, view, layout) {
    const { nutX, fretW, frets } = layout;

    if (fret <= 0) {
        return nutX - 12;
    }

    if (view.useNut) {
        if (fret > frets) {
            return null;
        }
        return nutX + (fret - 0.5) * fretW;
    }

    const slot = fret - view.baseFret + 1;
    if (slot < 1 || slot > frets) {
        return null;
    }

    return nutX + (slot - 0.5) * fretW;
}

function isBlackKey(semitone) {
    return [1, 3, 6, 8, 10].includes(semitone % 12);
}

export function paintGuitarSvg(svgEl, representation, layoutOverrides = {}) {
    const layout = {
        nutX: 40,
        fretW: 20,
        topY: 20,
        stringGap: 22,
        frets: 12,
        width: 300,
        height: 168,
        ...layoutOverrides,
    };
    const numeric = parseGuitarFrets(representation);
    const view = getDiagramView(numeric, layout);
    const ns = 'http://www.w3.org/2000/svg';
    const stringBottom = layout.topY + 5 * layout.stringGap;

    svgEl.setAttribute('viewBox', `0 0 ${layout.width} ${layout.height}`);
    svgEl.innerHTML = '';

    const bg = document.createElementNS(ns, 'rect');
    bg.setAttribute('width', String(layout.width));
    bg.setAttribute('height', String(layout.height));
    bg.setAttribute('fill', layout.bgFill || '#141c2e');
    bg.setAttribute('rx', '6');
    svgEl.appendChild(bg);

    if (!view.useNut) {
        const barreX = layout.nutX;
        for (const offset of [0, 2.5]) {
            const barre = document.createElementNS(ns, 'line');
            barre.setAttribute('x1', String(barreX + offset));
            barre.setAttribute('x2', String(barreX + offset));
            barre.setAttribute('y1', String(layout.topY));
            barre.setAttribute('y2', String(stringBottom));
            barre.setAttribute('stroke', 'rgba(255,255,255,0.55)');
            barre.setAttribute('stroke-width', '2');
            svgEl.appendChild(barre);
        }

        const fretLabel = document.createElementNS(ns, 'text');
        fretLabel.setAttribute('x', String(layout.nutX - 10));
        fretLabel.setAttribute('y', String(layout.topY + 2.5 * layout.stringGap + 4));
        fretLabel.setAttribute('text-anchor', 'middle');
        fretLabel.setAttribute('fill', '#94a3b8');
        fretLabel.setAttribute('font-size', '9');
        fretLabel.setAttribute('font-family', 'monospace');
        fretLabel.setAttribute('font-weight', 'bold');
        fretLabel.textContent = String(view.baseFret);
        svgEl.appendChild(fretLabel);
    }

    for (let f = 0; f <= layout.frets; f++) {
        if (f === 0 && !view.useNut) {
            continue;
        }

        const x = layout.nutX + f * layout.fretW;
        const line = document.createElementNS(ns, 'line');
        line.setAttribute('x1', String(x));
        line.setAttribute('y1', String(layout.topY));
        line.setAttribute('x2', String(x));
        line.setAttribute('y2', String(stringBottom));
        line.setAttribute('stroke', f === 0 && view.useNut ? 'rgba(255,255,255,0.45)' : 'rgba(255,255,255,0.15)');
        line.setAttribute('stroke-width', f === 0 && view.useNut ? '3' : '1');
        svgEl.appendChild(line);

        if (view.useNut && f > 0 && layout.showFretNumbers !== false && f <= 5) {
            const num = document.createElementNS(ns, 'text');
            num.setAttribute('x', String(x - layout.fretW / 2));
            num.setAttribute('y', String(stringBottom + 12));
            num.setAttribute('text-anchor', 'middle');
            num.setAttribute('fill', '#64748b');
            num.setAttribute('font-size', '8');
            num.setAttribute('font-family', 'monospace');
            num.textContent = String(f);
            svgEl.appendChild(num);
        }
    }

    const dotsGroup = document.createElementNS(ns, 'g');

    for (let s = 0; s < 6; s++) {
        const y = layout.topY + s * layout.stringGap;
        const stringLine = document.createElementNS(ns, 'line');
        stringLine.setAttribute('x1', String(layout.nutX - 6));
        stringLine.setAttribute('y1', String(y));
        stringLine.setAttribute('x2', String(layout.nutX + layout.frets * layout.fretW));
        stringLine.setAttribute('y2', String(y));
        stringLine.setAttribute('stroke', 'rgba(148,163,184,0.45)');
        stringLine.setAttribute('stroke-width', '1');
        svgEl.appendChild(stringLine);

        if (layout.showStringLabels) {
            const stringLabel = document.createElementNS(ns, 'text');
            stringLabel.setAttribute('x', String(layout.stringLabelX ?? 12));
            stringLabel.setAttribute('y', String(y + 4));
            stringLabel.setAttribute('fill', '#64748b');
            stringLabel.setAttribute('font-size', '10');
            stringLabel.setAttribute('font-family', 'monospace');
            stringLabel.textContent = STRING_LABELS[s];
            svgEl.appendChild(stringLabel);
        }

        const fret = numeric[s] ?? -1;
        if (fret === -1) {
            const mute = document.createElementNS(ns, 'text');
            mute.setAttribute('x', String(layout.nutX - 14));
            mute.setAttribute('y', String(y + 4));
            mute.setAttribute('fill', '#f87171');
            mute.setAttribute('font-size', '10');
            mute.setAttribute('font-weight', 'bold');
            mute.textContent = '×';
            dotsGroup.appendChild(mute);
            continue;
        }

        const cx = fretCenterX(fret, view, layout);
        if (cx === null) {
            continue;
        }

        if (fret === 0) {
            const openDot = document.createElementNS(ns, 'circle');
            openDot.setAttribute('cx', String(cx));
            openDot.setAttribute('cy', String(y));
            openDot.setAttribute('r', '4');
            openDot.setAttribute('fill', 'none');
            openDot.setAttribute('stroke', layout.dotFill || '#f59e0b');
            openDot.setAttribute('stroke-width', '1.5');
            dotsGroup.appendChild(openDot);
        } else {
            const dot = document.createElementNS(ns, 'circle');
            dot.setAttribute('cx', String(cx));
            dot.setAttribute('cy', String(y));
            dot.setAttribute('r', layout.dotR || 5);
            dot.setAttribute('fill', layout.dotFill || '#f59e0b');
            dotsGroup.appendChild(dot);
        }
    }

    svgEl.appendChild(dotsGroup);
}

export function paintKeyboardSvg(svgEl, notes, options = {}) {
    const ns = 'http://www.w3.org/2000/svg';
    const pianoStart = options.start ?? 60;
    const semitones = options.semitones ?? 18;
    const activeClasses = new Set(
        (notes || []).map((n) => pitchClassFromNote(n)).filter((pc) => pc !== null)
    );
    const whiteW = options.whiteW ?? 22;
    const whiteH = options.whiteH ?? 72;
    const blackW = options.blackW ?? 14;
    const blackH = options.blackH ?? 44;
    const width = options.width ?? 320;
    const height = options.height ?? 100;

    let x = 8;
    const whitePositions = [];
    svgEl.innerHTML = '';
    svgEl.setAttribute('viewBox', `0 0 ${width} ${height}`);

    const bg = document.createElementNS(ns, 'rect');
    bg.setAttribute('width', String(width));
    bg.setAttribute('height', String(height));
    bg.setAttribute('fill', options.bgFill || '#141c2e');
    bg.setAttribute('rx', '6');
    svgEl.appendChild(bg);

    for (let i = 0; i < semitones; i++) {
        const midi = pianoStart + i;
        const pc = midi % 12;
        if (!isBlackKey(i)) {
            const active = activeClasses.has(pc);
            const rect = document.createElementNS(ns, 'rect');
            rect.setAttribute('x', String(x));
            rect.setAttribute('y', '12');
            rect.setAttribute('width', String(whiteW));
            rect.setAttribute('height', String(whiteH));
            rect.setAttribute('rx', '2');
            rect.setAttribute('fill', active ? (options.activeFill || '#f59e0b') : (options.inactiveFill || '#334155'));
            rect.setAttribute('stroke', active ? (options.activeStroke || '#fcd34d') : 'rgba(255,255,255,0.08)');
            svgEl.appendChild(rect);
            whitePositions.push({ x });
            x += whiteW;
        }
    }

    let whiteIdx = 0;
    for (let i = 0; i < semitones; i++) {
        if (isBlackKey(i)) {
            const pc = (pianoStart + i) % 12;
            const active = activeClasses.has(pc);
            const prev = whitePositions[whiteIdx - 1];
            if (prev) {
                const rect = document.createElementNS(ns, 'rect');
                rect.setAttribute('x', String(prev.x + whiteW - blackW / 2));
                rect.setAttribute('y', '12');
                rect.setAttribute('width', String(blackW));
                rect.setAttribute('height', String(blackH));
                rect.setAttribute('rx', '2');
                rect.setAttribute('fill', active ? (options.activeFillDark || options.activeFill || '#d97706') : '#1e293b');
                svgEl.appendChild(rect);
            }
        } else {
            whiteIdx++;
        }
    }
}

export const CAROUSEL_GUITAR_LAYOUT = {
    nutX: 14,
    fretW: 9,
    topY: 14,
    stringGap: 11,
    frets: 5,
    width: 72,
    height: 88,
    dotR: 3.5,
    bgFill: '#1a2236',
    showFretNumbers: false,
};

export const DICTIONARY_GUITAR_LAYOUT = {
    nutX: 40,
    fretW: 20,
    topY: 20,
    stringGap: 22,
    frets: 12,
    width: 300,
    height: 168,
    bgFill: '#0a0f1a',
    dotFill: '#8b5cf6',
    dotR: 6,
    showStringLabels: true,
};
