/**
 * Herramientas del músico — afinador, metrónomo, círculo de quintas.
 */

const NOTE_NAMES = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
const NOTE_NAMES_FLAT = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];

const GUITAR_STRINGS = {
    E: 82.41,
    A: 110.0,
    D: 146.83,
    G: 196.0,
    B: 246.94,
    e: 329.63,
};

const CIRCLE_KEYS = [
    { major: 'Do', majorEn: 'C', minor: 'Lam', minorEn: 'Am' },
    { major: 'Sol', majorEn: 'G', minor: 'Mim', minorEn: 'Em' },
    { major: 'Re', majorEn: 'D', minor: 'Sim', minorEn: 'Bm' },
    { major: 'La', majorEn: 'A', minor: 'Fa♯m', minorEn: 'F#m' },
    { major: 'Mi', majorEn: 'E', minor: 'Do♯m', minorEn: 'C#m' },
    { major: 'Si', majorEn: 'B', minor: 'Sol♯m', minorEn: 'G#m' },
    { major: 'Fa♯', majorEn: 'F#', minor: 'Re♯m', minorEn: 'D#m' },
    { major: 'Re♭', majorEn: 'Db', minor: 'Sibm', minorEn: 'Bbm' },
    { major: 'La♭', majorEn: 'Ab', minor: 'Fam', minorEn: 'Fm' },
    { major: 'Mi♭', majorEn: 'Eb', minor: 'Dom', minorEn: 'Cm' },
    { major: 'Si♭', majorEn: 'Bb', minor: 'Solm', minorEn: 'Gm' },
    { major: 'Fa', majorEn: 'F', minor: 'Rem', minorEn: 'Dm' },
];

const MAJOR_SCALE = [0, 2, 4, 5, 7, 9, 11];
const TRIAD_QUALITIES = ['major', 'minor', 'minor', 'major', 'major', 'minor', 'diminished'];
const DEGREE_LABELS = ['I', 'ii', 'iii', 'IV', 'V', 'vi', 'vii°'];
const HIGHLIGHT_DEGREES = new Set([0, 1, 2, 3, 4, 5]);

function noteIndex(name) {
    const idx = NOTE_NAMES.indexOf(name);
    if (idx !== -1) {
        return idx;
    }
    return NOTE_NAMES_FLAT.indexOf(name);
}

function freqToNote(freq) {
    if (!freq || freq < 20) {
        return null;
    }
    const midi = 12 * Math.log2(freq / 440) + 69;
    const rounded = Math.round(midi);
    const cents = Math.round((midi - rounded) * 100);
    const idx = ((rounded % 12) + 12) % 12;

    return { note: NOTE_NAMES[idx], cents, freq };
}

function detectPitch(buffer, sampleRate) {
    const size = buffer.length;
    let rms = 0;

    for (let i = 0; i < size; i++) {
        rms += buffer[i] * buffer[i];
    }
    rms = Math.sqrt(rms / size);
    if (rms < 0.008) {
        return null;
    }

    let r1 = 0;
    let r2 = size - 1;
    const threshold = 0.15;

    for (let i = 0; i < size / 2; i++) {
        if (Math.abs(buffer[i]) < threshold) {
            r1 = i;
            break;
        }
    }
    for (let i = 1; i < size / 2; i++) {
        if (Math.abs(buffer[size - i]) < threshold) {
            r2 = size - i;
            break;
        }
    }

    const trimmed = buffer.slice(r1, r2);
    const len = trimmed.length;
    const correlations = new Float32Array(len);

    for (let lag = 0; lag < len; lag++) {
        for (let i = 0; i < len - lag; i++) {
            correlations[lag] += trimmed[i] * trimmed[i + lag];
        }
    }

    let d = 0;
    while (d < len - 1 && correlations[d] > correlations[d + 1]) {
        d++;
    }

    let maxVal = -1;
    let maxPos = -1;
    for (let i = d; i < len; i++) {
        if (correlations[i] > maxVal) {
            maxVal = correlations[i];
            maxPos = i;
        }
    }

    if (maxPos <= 0) {
        return null;
    }

    let T0 = maxPos;
    const x1 = correlations[T0 - 1] ?? 0;
    const x2 = correlations[T0];
    const x3 = correlations[T0 + 1] ?? 0;
    const a = (x1 + x3 - 2 * x2) / 2;
    const b = (x3 - x1) / 2;
    if (a) {
        T0 -= b / (2 * a);
    }

    return sampleRate / T0;
}

function chordNameForDegree(rootIdx, interval, quality) {
    const note = NOTE_NAMES[(rootIdx + interval) % 12];
    if (quality === 'minor') {
        return `${note}m`;
    }
    if (quality === 'diminished') {
        return `${note}dim`;
    }
    return note;
}

function getScaleChords(rootEn) {
    const rootIdx = noteIndex(rootEn);
    if (rootIdx < 0) {
        return [];
    }

    return MAJOR_SCALE.map((interval, i) => ({
        degree: DEGREE_LABELS[i],
        chord: chordNameForDegree(rootIdx, interval, TRIAD_QUALITIES[i]),
        highlight: HIGHLIGHT_DEGREES.has(i),
    }));
}

function initTuner(root) {
    const tuner = root.querySelector('[data-tuner]');
    if (!tuner) {
        return;
    }

    const startBtn = tuner.querySelector('[data-tuner-mic-start]');
    const stopBtn = tuner.querySelector('[data-tuner-mic-stop]');
    const statusEl = tuner.querySelector('[data-tuner-status]');
    const needle = tuner.querySelector('[data-tuner-needle]');
    const noteEl = tuner.querySelector('[data-tuner-note]');
    const freqEl = tuner.querySelector('[data-tuner-freq]');
    const centsEl = tuner.querySelector('[data-tuner-cents]');

    let audioCtx = null;
    let analyser = null;
    let mediaStream = null;
    let rafId = null;
    let earCtx = null;

    function getEarCtx() {
        if (!earCtx) {
            earCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        return earCtx;
    }

    function playTone(freq, label) {
        const ctx = getEarCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.0001, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.35, ctx.currentTime + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 1.5);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 1.5);
        noteEl.textContent = label;
        freqEl.textContent = `${freq.toFixed(1)} Hz`;
        centsEl.textContent = 'Referencia por oído';
        needle.style.transform = 'translateX(-50%) rotate(0deg)';
    }

    function updateUI(detected) {
        if (!detected) {
            noteEl.textContent = '—';
            freqEl.textContent = '0 Hz';
            centsEl.textContent = 'Sin señal';
            needle.style.transform = 'translateX(-50%) rotate(0deg)';
            return;
        }

        const info = freqToNote(detected);
        if (!info) {
            return;
        }

        noteEl.textContent = info.note;
        freqEl.textContent = `${detected.toFixed(1)} Hz`;

        const cents = info.cents;
        const rotation = Math.max(-45, Math.min(45, cents * 0.9));
        needle.style.transform = `translateX(-50%) rotate(${rotation}deg)`;

        const needleBase = 'absolute bottom-2 left-1/2 h-[calc(100%-0.5rem)] w-0.5 origin-bottom -translate-x-1/2 rounded-full transition-transform duration-75';

        if (Math.abs(cents) <= 5) {
            centsEl.textContent = '✓ Afinado';
            centsEl.className = 'text-xs text-emerald-400';
            needle.className = `${needleBase} bg-emerald-400`;
        } else if (cents < 0) {
            centsEl.textContent = `${Math.abs(cents)}¢ bemol (baja)`;
            centsEl.className = 'text-xs text-amber-400';
            needle.className = `${needleBase} bg-amber-400`;
        } else {
            centsEl.textContent = `${cents}¢ sostenido (alta)`;
            centsEl.className = 'text-xs text-rose-400';
            needle.className = `${needleBase} bg-rose-400`;
        }
    }

    function tick() {
        if (!analyser) {
            return;
        }
        const buffer = new Float32Array(analyser.fftSize);
        analyser.getFloatTimeDomainData(buffer);
        updateUI(detectPitch(buffer, audioCtx.sampleRate));
        rafId = requestAnimationFrame(tick);
    }

    async function startMic() {
        try {
            mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const source = audioCtx.createMediaStreamSource(mediaStream);
            analyser = audioCtx.createAnalyser();
            analyser.fftSize = 2048;
            source.connect(analyser);
            startBtn.classList.add('hidden');
            stopBtn.classList.remove('hidden');
            statusEl.textContent = 'Escuchando… toca una cuerda cerca del micrófono.';
            tick();
        } catch {
            statusEl.textContent = 'No se pudo acceder al micrófono. Usa el modo por oído.';
        }
    }

    function stopMic() {
        if (rafId) {
            cancelAnimationFrame(rafId);
            rafId = null;
        }
        mediaStream?.getTracks().forEach((t) => t.stop());
        mediaStream = null;
        audioCtx?.close();
        audioCtx = null;
        analyser = null;
        startBtn.classList.remove('hidden');
        stopBtn.classList.add('hidden');
        statusEl.textContent = 'Micrófono detenido.';
        updateUI(null);
    }

    startBtn?.addEventListener('click', startMic);
    stopBtn?.addEventListener('click', stopMic);

    tuner.querySelectorAll('[data-tuner-string]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const label = btn.dataset.tunerString;
            playTone(GUITAR_STRINGS[label], label);
        });
    });
}

function initMetronome(root) {
    const metro = root.querySelector('[data-metronome]');
    if (!metro) {
        return;
    }

    const bpmInput = metro.querySelector('[data-metronome-bpm]');
    const bpmDisplay = metro.querySelector('[data-metronome-bpm-display]');
    const beatsEl = metro.querySelector('[data-metronome-beats]');
    const toggleBtn = metro.querySelector('[data-metronome-toggle]');
    const sigBtns = metro.querySelectorAll('[data-metronome-signature]');

    let audioCtx = null;
    let isPlaying = false;
    let bpm = 120;
    let signature = '4/4';
    let beatsPerBar = 4;
    let accentBeats = new Set([0]);
    let currentBeat = 0;
    let nextNoteTime = 0;
    let schedulerTimer = null;

    function getCtx() {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        return audioCtx;
    }

    function signatureConfig(sig) {
        if (sig === '3/4') {
            return { beats: 3, accents: new Set([0]) };
        }
        if (sig === '6/8') {
            return { beats: 6, accents: new Set([0, 3]) };
        }
        return { beats: 4, accents: new Set([0]) };
    }

    function renderBeats() {
        beatsEl.innerHTML = '';
        for (let i = 0; i < beatsPerBar; i++) {
            const dot = document.createElement('span');
            dot.className = `h-3 w-3 rounded-full border border-white/20 transition ${accentBeats.has(i) ? 'bg-violet-600/30' : 'bg-white/5'}`;
            dot.dataset.beatDot = String(i);
            beatsEl.appendChild(dot);
        }
    }

    function flashBeat(beat) {
        beatsEl.querySelectorAll('[data-beat-dot]').forEach((dot, i) => {
            const active = i === beat;
            const accent = accentBeats.has(i);
            dot.className = active
                ? `h-3 w-3 rounded-full border transition ${accent ? 'border-violet-400 bg-violet-500 shadow shadow-violet-500/50' : 'border-white/40 bg-white/30'}`
                : `h-3 w-3 rounded-full border border-white/20 transition ${accent ? 'bg-violet-600/30' : 'bg-white/5'}`;
        });
    }

    function playClick(time, accent) {
        const ctx = getCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.frequency.value = accent ? 1200 : 800;
        gain.gain.setValueAtTime(0.0001, time);
        gain.gain.exponentialRampToValueAtTime(accent ? 0.5 : 0.3, time + 0.002);
        gain.gain.exponentialRampToValueAtTime(0.0001, time + 0.05);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(time);
        osc.stop(time + 0.06);
    }

    function schedule() {
        const ctx = getCtx();
        const secondsPerBeat = 60 / bpm;

        while (nextNoteTime < ctx.currentTime + 0.12) {
            const accent = accentBeats.has(currentBeat);
            playClick(nextNoteTime, accent);

            const beatToFlash = currentBeat;
            setTimeout(() => flashBeat(beatToFlash), Math.max(0, (nextNoteTime - ctx.currentTime) * 1000));

            nextNoteTime += secondsPerBeat;
            currentBeat = (currentBeat + 1) % beatsPerBar;
        }
    }

    function start() {
        const ctx = getCtx();
        if (ctx.state === 'suspended') {
            ctx.resume();
        }
        isPlaying = true;
        currentBeat = 0;
        nextNoteTime = ctx.currentTime + 0.05;
        toggleBtn.textContent = '■ Detener';
        toggleBtn.classList.replace('bg-violet-600', 'bg-rose-600/80');
        toggleBtn.classList.replace('hover:bg-violet-500', 'hover:bg-rose-500/80');
        schedulerTimer = setInterval(schedule, 25);
    }

    function stop() {
        isPlaying = false;
        clearInterval(schedulerTimer);
        schedulerTimer = null;
        toggleBtn.textContent = '▶ Iniciar';
        toggleBtn.classList.replace('bg-rose-600/80', 'bg-violet-600');
        toggleBtn.classList.replace('hover:bg-rose-500/80', 'hover:bg-violet-500');
        renderBeats();
    }

    bpmInput?.addEventListener('input', () => {
        bpm = Number(bpmInput.value);
        bpmDisplay.textContent = String(bpm);
    });

    sigBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            signature = btn.dataset.metronomeSignature;
            const cfg = signatureConfig(signature);
            beatsPerBar = cfg.beats;
            accentBeats = cfg.accents;
            sigBtns.forEach((b) => {
                const active = b === btn;
                b.className = `flex-1 rounded-lg border px-2 py-1.5 text-xs font-medium transition ${active ? 'border-violet-500/50 bg-violet-600/20 text-violet-200' : 'border-white/10 text-slate-400 hover:bg-white/5'}`;
            });
            renderBeats();
            if (isPlaying) {
                stop();
                start();
            }
        });
    });

    toggleBtn?.addEventListener('click', () => {
        if (isPlaying) {
            stop();
        } else {
            start();
        }
    });

    renderBeats();
}

function polarToCartesian(cx, cy, r, angleDeg) {
    const rad = ((angleDeg - 90) * Math.PI) / 180;
    return { x: cx + r * Math.cos(rad), y: cy + r * Math.sin(rad) };
}

function describeArc(cx, cy, rInner, rOuter, startAngle, endAngle) {
    const startOuter = polarToCartesian(cx, cy, rOuter, endAngle);
    const endOuter = polarToCartesian(cx, cy, rOuter, startAngle);
    const startInner = polarToCartesian(cx, cy, rInner, startAngle);
    const endInner = polarToCartesian(cx, cy, rInner, endAngle);
    const largeArc = endAngle - startAngle <= 180 ? 0 : 1;

    return [
        `M ${startOuter.x} ${startOuter.y}`,
        `A ${rOuter} ${rOuter} 0 ${largeArc} 0 ${endOuter.x} ${endOuter.y}`,
        `L ${startInner.x} ${startInner.y}`,
        `A ${rInner} ${rInner} 0 ${largeArc} 1 ${endInner.x} ${endInner.y}`,
        'Z',
    ].join(' ');
}

function initCircleOfFifths(root) {
    const section = root.querySelector('[data-circle-of-fifths]');
    if (!section) {
        return;
    }

    const svg = section.querySelector('[data-circle-svg]');
    const selectedEl = section.querySelector('[data-circle-selected]');
    const chordsEl = section.querySelector('[data-circle-chords]');

    const cx = 160;
    const cy = 160;
    const count = CIRCLE_KEYS.length;
    const slice = 360 / count;
    let selectedIndex = null;

    function renderCircle() {
        svg.innerHTML = '';

        const bg = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        bg.setAttribute('cx', String(cx));
        bg.setAttribute('cy', String(cy));
        bg.setAttribute('r', '150');
        bg.setAttribute('fill', '#0a0f1a');
        bg.setAttribute('stroke', 'rgba(255,255,255,0.08)');
        svg.appendChild(bg);

        CIRCLE_KEYS.forEach((key, i) => {
            const start = i * slice - slice / 2;
            const end = start + slice;
            const isSelected = selectedIndex === i;

            const group = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            group.style.cursor = 'pointer';

            const outerPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            outerPath.setAttribute('d', describeArc(cx, cy, 95, 150, start, end));
            outerPath.setAttribute('fill', isSelected ? 'rgba(124,58,237,0.45)' : 'rgba(255,255,255,0.04)');
            outerPath.setAttribute('stroke', isSelected ? 'rgba(167,139,250,0.8)' : 'rgba(255,255,255,0.06)');
            outerPath.setAttribute('stroke-width', '1');

            const innerPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            innerPath.setAttribute('d', describeArc(cx, cy, 55, 94, start, end));
            innerPath.setAttribute('fill', isSelected ? 'rgba(124,58,237,0.25)' : 'rgba(255,255,255,0.02)');
            innerPath.setAttribute('stroke', 'rgba(255,255,255,0.05)');
            innerPath.setAttribute('stroke-width', '1');

            const midAngle = start + slice / 2;
            const majorPos = polarToCartesian(cx, cy, 122, midAngle);
            const minorPos = polarToCartesian(cx, cy, 74, midAngle);

            const majorText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            majorText.setAttribute('x', String(majorPos.x));
            majorText.setAttribute('y', String(majorPos.y));
            majorText.setAttribute('text-anchor', 'middle');
            majorText.setAttribute('dominant-baseline', 'middle');
            majorText.setAttribute('fill', isSelected ? '#e9d5ff' : '#cbd5e1');
            majorText.setAttribute('font-size', '13');
            majorText.setAttribute('font-weight', '600');
            majorText.textContent = key.major;

            const minorText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            minorText.setAttribute('x', String(minorPos.x));
            minorText.setAttribute('y', String(minorPos.y));
            minorText.setAttribute('text-anchor', 'middle');
            minorText.setAttribute('dominant-baseline', 'middle');
            minorText.setAttribute('fill', isSelected ? '#c4b5fd' : '#64748b');
            minorText.setAttribute('font-size', '10');
            minorText.textContent = key.minor;

            group.appendChild(outerPath);
            group.appendChild(innerPath);
            group.appendChild(majorText);
            group.appendChild(minorText);

            group.addEventListener('click', () => selectKey(i));
            svg.appendChild(group);
        });

        const center = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        center.setAttribute('cx', String(cx));
        center.setAttribute('cy', String(cy));
        center.setAttribute('r', '52');
        center.setAttribute('fill', '#141c2e');
        center.setAttribute('stroke', 'rgba(255,255,255,0.08)');
        svg.appendChild(center);

        const centerText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        centerText.setAttribute('x', String(cx));
        centerText.setAttribute('y', String(cy));
        centerText.setAttribute('text-anchor', 'middle');
        centerText.setAttribute('dominant-baseline', 'middle');
        centerText.setAttribute('fill', '#94a3b8');
        centerText.setAttribute('font-size', '11');
        centerText.textContent = selectedIndex !== null ? CIRCLE_KEYS[selectedIndex].major : 'Quintas';
        svg.appendChild(centerText);
    }

    function renderChords(key) {
        const chords = getScaleChords(key.majorEn);
        selectedEl.textContent = `${key.major} mayor · relativa ${key.minor}`;
        chordsEl.innerHTML = chords
            .map((c) => {
                const cls = c.highlight
                    ? 'border-violet-500/50 bg-violet-600/25 text-violet-100'
                    : 'border-white/10 bg-white/5 text-slate-500';
                return `<span class="inline-flex flex-col items-center rounded-lg border px-2.5 py-1.5 text-center ${cls}">
                    <span class="font-mono text-sm font-semibold">${c.chord}</span>
                    <span class="text-[10px] opacity-70">${c.degree}</span>
                </span>`;
            })
            .join('');
    }

    function selectKey(index) {
        selectedIndex = index;
        renderCircle();
        renderChords(CIRCLE_KEYS[index]);
    }

    renderCircle();
}

function initMusicianTools() {
    const root = document.querySelector('[data-musician-tools]');
    if (!root) {
        return;
    }

    initTuner(root);
    initMetronome(root);
    initCircleOfFifths(root);
}

document.addEventListener('DOMContentLoaded', initMusicianTools);
