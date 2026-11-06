export const NOTE_NAMES = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
const NOTE_NAMES_FLAT = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];
const NOTE_INDEX = new Map();
NOTE_NAMES.forEach((note, index) => NOTE_INDEX.set(note, index));
NOTE_NAMES_FLAT.forEach((note, index) => NOTE_INDEX.set(note, index));

export const INSTRUMENT_PRESETS = {
    guitar: {
        id: 'guitar',
        name: 'Guitarra',
        tunings: [
            { id: 'standard', name: 'Estándar (E A D G B E)', strings: [
                { label: 'E', note: 'E', octave: 2 }, { label: 'A', note: 'A', octave: 2 },
                { label: 'D', note: 'D', octave: 3 }, { label: 'G', note: 'G', octave: 3 },
                { label: 'B', note: 'B', octave: 3 }, { label: 'e', note: 'E', octave: 4 },
            ] },
            { id: 'drop-d', name: 'Drop D (D A D G B E)', strings: [
                { label: 'D', note: 'D', octave: 2 }, { label: 'A', note: 'A', octave: 2 },
                { label: 'D', note: 'D', octave: 3 }, { label: 'G', note: 'G', octave: 3 },
                { label: 'B', note: 'B', octave: 3 }, { label: 'e', note: 'E', octave: 4 },
            ] },
        ],
    },
    bass: {
        id: 'bass',
        name: 'Bajo',
        tunings: [
            { id: 'standard', name: 'Estándar (E A D G)', strings: [
                { label: 'E', note: 'E', octave: 1 }, { label: 'A', note: 'A', octave: 1 },
                { label: 'D', note: 'D', octave: 2 }, { label: 'G', note: 'G', octave: 2 },
            ] },
            { id: 'drop-d', name: 'Drop D (D A D G)', strings: [
                { label: 'D', note: 'D', octave: 1 }, { label: 'A', note: 'A', octave: 1 },
                { label: 'D', note: 'D', octave: 2 }, { label: 'G', note: 'G', octave: 2 },
            ] },
        ],
    },
};

export function getInstrumentPreset(instrumentId) {
    return INSTRUMENT_PRESETS[instrumentId] ?? INSTRUMENT_PRESETS.guitar;
}

export function getTuningPreset(instrumentId, tuningId = 'standard') {
    const instrument = getInstrumentPreset(instrumentId);
    return instrument.tunings.find((tuning) => tuning.id === tuningId) ?? instrument.tunings[0];
}

export function freqToNote(frequency, referencePitch = 440) {
    if (!Number.isFinite(frequency) || frequency <= 0 || !Number.isFinite(referencePitch) || referencePitch <= 0) return null;
    const midiFloat = 69 + 12 * Math.log2(frequency / referencePitch);
    const midi = Math.round(midiFloat);
    const noteIndex = ((midi % 12) + 12) % 12;
    return { note: NOTE_NAMES[noteIndex], octave: Math.floor(midi / 12) - 1, midi, cents: Math.round((midiFloat - midi) * 100), frequency };
}

export function noteToFrequency(note, octave, referencePitch = 440) {
    const noteIndex = NOTE_INDEX.get(note);
    if (noteIndex === undefined || !Number.isInteger(octave) || !Number.isFinite(referencePitch) || referencePitch <= 0) return null;
    const midi = (octave + 1) * 12 + noteIndex;
    return referencePitch * (2 ** ((midi - 69) / 12));
}

export function centsBetween(frequency, targetFrequency) {
    if (!Number.isFinite(frequency) || frequency <= 0 || !Number.isFinite(targetFrequency) || targetFrequency <= 0) return null;
    return 1200 * Math.log2(frequency / targetFrequency);
}