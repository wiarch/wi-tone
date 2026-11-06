/** YIN-style fundamental-frequency estimator with bounded, decimated analysis. */
export function detectPitch(buffer, sampleRate) {
    if (!buffer || typeof buffer.length !== 'number' || buffer.length < 256 || !Number.isFinite(sampleRate) || sampleRate <= 0) return null;
    const decimation = 4;
    const sampleCount = Math.floor(buffer.length / decimation);
    const samples = new Float32Array(sampleCount);
    let energy = 0;
    for (let index = 0; index < sampleCount; index++) {
        const sample = buffer[index * decimation];
        samples[index] = sample;
        energy += sample * sample;
    }
    const rms = Math.sqrt(energy / sampleCount);
    if (rms < 0.008) return null;
    const effectiveRate = sampleRate / decimation;
    const minLag = Math.max(2, Math.floor(effectiveRate / 1200));
    const maxLag = Math.min(Math.floor(effectiveRate / 30), Math.floor(sampleCount / 2));
    if (maxLag <= minLag) return null;
    const differences = new Float64Array(maxLag + 1);
    for (let lag = minLag; lag <= maxLag; lag++) {
        let difference = 0;
        const limit = sampleCount - lag;
        for (let index = 0; index < limit; index++) {
            const delta = samples[index] - samples[index + lag];
            difference += delta * delta;
        }
        differences[lag] = difference;
    }
    let cumulative = 0;
    const normalized = new Float64Array(maxLag + 1);
    for (let lag = minLag; lag <= maxLag; lag++) {
        cumulative += differences[lag];
        normalized[lag] = cumulative === 0 ? 1 : differences[lag] * (lag - minLag + 1) / cumulative;
    }
    const threshold = 0.18;
    let selectedLag = -1;
    for (let lag = minLag; lag <= maxLag; lag++) {
        if (normalized[lag] < threshold) {
            while (lag + 1 <= maxLag && normalized[lag + 1] < normalized[lag]) lag++;
            selectedLag = lag;
            break;
        }
    }
    if (selectedLag === -1) {
        let bestValue = Infinity;
        for (let lag = minLag; lag <= maxLag; lag++) {
            if (normalized[lag] < bestValue) { bestValue = normalized[lag]; selectedLag = lag; }
        }
        if (selectedLag === -1 || normalized[selectedLag] > 0.35) return null;
    }
    const previous = normalized[selectedLag - 1] ?? normalized[selectedLag];
    const current = normalized[selectedLag];
    const next = normalized[selectedLag + 1] ?? current;
    const denominator = previous - 2 * current + next;
    const adjustment = denominator === 0 ? 0 : 0.5 * (previous - next) / denominator;
    const refinedLag = selectedLag + Math.max(-1, Math.min(1, adjustment));
    const frequency = effectiveRate / refinedLag;
    if (!Number.isFinite(frequency) || frequency < 30 || frequency > 1200) return null;
    return { frequency, clarity: Math.max(0, Math.min(1, 1 - current)), rms };
}