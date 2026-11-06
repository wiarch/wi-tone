import test from 'node:test';
import assert from 'node:assert/strict';
import { detectPitch, frequencyToNote, getTuningPreset, INSTRUMENT_PRESETS } from '../../resources/js/music/pitch-detection.js';

function sineWave(frequency, sampleRate = 48000, length = 4096, amplitude = 0.35) {
    return Float32Array.from(
        { length },
        (_, index) => amplitude * Math.sin((2 * Math.PI * frequency * index) / sampleRate),
    );
}

test('converts concert A to A4 with zero cents deviation', () => {
    const note = frequencyToNote(440);
    assert.equal(note.note, 'A');
    assert.equal(note.octave, 4);
    assert.equal(note.cents, 0);
});

test('converts standard guitar E2 frequency to E2', () => {
    const note = frequencyToNote(82.4069);
    assert.equal(note.note, 'E');
    assert.equal(note.octave, 2);
    assert.ok(Math.abs(note.cents) <= 1);
});

test('rejects invalid frequencies and reference pitches', () => {
    assert.equal(frequencyToNote(0), null);
    assert.equal(frequencyToNote(Number.NaN), null);
    assert.equal(frequencyToNote(440, 0), null);
});

test('detects a guitar low E fundamental', () => {
    const result = detectPitch(sineWave(82.4069), 48000);
    assert.ok(result);
    assert.ok(Math.abs(result.frequency - 82.4069) < 1);
    assert.ok(result.confidence >= 0.72);
});

test('detects a low bass E fundamental', () => {
    const result = detectPitch(sineWave(41.2034), 48000, { minFrequency: 35 });
    assert.ok(result);
    assert.ok(Math.abs(result.frequency - 41.2034) < 1);
});

test('returns no pitch for silence', () => {
    assert.equal(detectPitch(new Float32Array(4096), 48000), null);
});

test('returns no pitch for malformed input', () => {
    assert.equal(detectPitch(null, 48000), null);
    assert.equal(detectPitch(new Float32Array(100), 48000), null);
    assert.equal(detectPitch(new Float32Array(4096), 0), null);
});

test('provides standard guitar and bass tuning reference data', () => {
    const guitar = getTuningPreset('guitar', 'standard');
    const bass = getTuningPreset('bass', 'standard');

    assert.equal(guitar.strings.length, 6);
    assert.equal(bass.strings.length, 4);
    assert.equal(guitar.strings[0].note, 'E2');
    assert.equal(bass.strings[0].note, 'E1');
    assert.equal(getTuningPreset('guitar', 'dropD').strings[0].note, 'D2');
    assert.equal(getTuningPreset('bass', 'dropD').strings[0].note, 'D1');
    assert.equal(getTuningPreset('ukulele', 'standard'), null);
    assert.deepEqual(Object.keys(INSTRUMENT_PRESETS), ['guitar', 'bass']);
});
