/**
 * Pure audio and tuning primitives for WI Tone.
 * No DOM or browser globals: safe to exercise with Node's test runner.
 */

export const NOTE_NAMES = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

export const INSTRUMENT_PRESETS = {
    guitar: {
        label: 'Guitarra',
        tunings: {
            standard: {
                label: 'Estándar (E A D G B E)',
                strings: [
                    { id: 'E2', label: 'E', note: 'E2', frequency: 82.4069 },
                    { id: 'A2', label: 'A', note: 'A2', frequency: 110.0 },
                    { id: 'D3', label: 'D', note: 'D3', frequency: 146.8324 },
                    { id: 'G3', label: 'G', note: 'G3', frequency: 195.9977 },
                    { id: 'B3', label: 'B', note: 'B3', frequency: 246.9417 },
                    { id: 'E4', label: 'e', note: 'E4', frequency: 329.6276 },
                ],
            },
        },
    },
    bass: {
        label: 'Bajo',
        tunings: {
            standard: {
                label: 'Estándar (E A D G)',
                strings: [
                    { id: 'E1', label: 'E', note: 'E1', frequency: 41.2034 },
                    { id: 'A1', label: 'A', note: 'A1', frequency: 55.0 },
                    { id: 'D2', label: 'D', note: 'D2', frequency: 73.4162 },
                    { id: 'G2', label: 'G', note: 'G2', frequency: 97.9989 },
                ],
            },
        },
    },
};

export function frequencyToNote(frequency, referenceA4 = 440) {
    if (!Number.isFinite(frequency) || frequency <= 0 || !Number.isFinite(referenceA4) || referenceA4 <= 0) {
        return null;
    }

    const midi = 69 + 12 * Math.log2(frequency / referenceA4);
    const roundedMidi = Math.round(midi);
    const noteIndex = ((roundedMidi % 12) + 12) % 12;

    return {
        note: NOTE_NAMES[noteIndex],
        octave: Math.floor(roundedMidi / 12) - 1,
        midi: roundedMidi,
        cents: Math.round((midi - roundedMidi) * 100),
        frequency,
    };
}

/**
 * Estimate the fundamental with normalized autocorrelation.
 * Returns quality metrics instead of a bare frequency so callers can
 * reject weak/noisy estimates rather than presenting false precision.
 */
export function detectPitch(buffer, sampleRate, options = {}) {
    if (!buffer || typeof buffer.length !== 'number' || buffer.length < 256 ||
        !Number.isFinite(sampleRate) || sampleRate <= 0) {
        return null;
    }

    const minFrequency = options.minFrequency ?? 35;
    const maxFrequency = options.maxFrequency ?? 1000;
    const minRms = options.minRms ?? 0.006;
    const threshold = options.confidenceThreshold ?? 0.72;

    let mean = 0;
    for (let i = 0; i < buffer.length; i++) mean += buffer[i];
    mean /= buffer.length;

    let energy = 0;
    for (let i = 0; i < buffer.length; i++) {
        const centered = buffer[i] - mean;
        energy += centered * centered;
    }

    const rms = Math.sqrt(energy / buffer.length);
    if (!Number.isFinite(rms) || rms < minRms) return null;

    const minLag = Math.max(2, Math.floor(sampleRate / maxFrequency));
    const maxLag = Math.min(
        Math.floor(sampleRate / minFrequency),
        Math.floor(buffer.length * 0.7),
    );

    if (maxLag <= minLag) return null;

    let bestLag = -1;
    let bestCorrelation = -1;
    const correlations = new Float32Array(maxLag + 1);

    for (let lag = minLag; lag <= maxLag; lag++) {
        let cross = 0;
        let leftEnergy = 0;
        let rightEnergy = 0;
        const limit = buffer.length - lag;

        for (let i = 0; i < limit; i++) {
            const left = buffer[i] - mean;
            const right = buffer[i + lag] - mean;
            cross += left * right;
            leftEnergy += left * left;
            rightEnergy += right * right;
        }

        const denominator = Math.sqrt(leftEnergy * rightEnergy);
        const correlation = denominator > 0 ? cross / denominator : 0;
        correlations[lag] = correlation;

        if (correlation > bestCorrelation) {
            bestCorrelation = correlation;
            bestLag = lag;
        }
    }

    if (bestLag < 0 || bestCorrelation < threshold) return null;

    // Prefer the earliest strong local maximum to reduce octave-down errors.
    const acceptable = bestCorrelation * 0.96;
    for (let lag = minLag + 1; lag < bestLag; lag++) {
        const value = correlations[lag];
        if (value >= acceptable && value >= correlations[lag - 1] && value >= correlations[lag + 1]) {
            bestLag = lag;
            bestCorrelation = value;
            break;
        }
    }

    // Parabolic interpolation improves the lag estimate between samples.
    const previous = correlations[bestLag - 1] ?? bestCorrelation;
    const current = correlations[bestLag];
    const next = correlations[bestLag + 1] ?? bestCorrelation;
    const denominator = previous - 2 * current + next;
    const adjustment = Math.abs(denominator) > 1e-9
        ? 0.5 * (previous - next) / denominator
        : 0;
    const refinedLag = bestLag + Math.max(-0.5, Math.min(0.5, adjustment));
    const frequency = sampleRate / refinedLag;

    if (!Number.isFinite(frequency) || frequency < minFrequency || frequency > maxFrequency) return null;

    return { frequency, confidence: bestCorrelation, rms };
}

export function getTuningPreset(instrument = 'guitar', tuning = 'standard') {
    return INSTRUMENT_PRESETS[instrument]?.tunings[tuning] ?? null;
}
