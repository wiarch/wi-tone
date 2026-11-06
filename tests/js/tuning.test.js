import test from 'node:test';
import assert from 'node:assert/strict';
import { centsBetween, freqToNote, getInstrumentPreset, noteToFrequency } from '../../resources/js/music/tuning.js';

test('converts A4 reference frequency to A4', () => {
    const result = freqToNote(440);
    assert.equal(result.note, 'A');
    assert.equal(result.octave, 4);
    assert.equal(result.cents, 0);
});

test('converts standard guitar and bass string frequencies to expected notes', () => {
    const guitarLowE = freqToNote(82.4069);
    const bassLowE = freqToNote(41.2034);
    assert.equal(guitarLowE.note + guitarLowE.octave, 'E2');
    assert.equal(bassLowE.note + bassLowE.octave, 'E1');
});

test('calculates note frequencies and cents deviation', () => {
    assert.ok(Math.abs(noteToFrequency('A', 4) - 440) < 0.001);
    assert.ok(Math.abs(centsBetween(440 * 2 ** (10 / 1200), 440) - 10) < 0.001);
});

test('provides standard guitar and bass presets', () => {
    assert.deepEqual(getInstrumentPreset('guitar').tunings[0].strings.map(s => s.note + s.octave), ['E2', 'A2', 'D3', 'G3', 'B3', 'E4']);
    assert.deepEqual(getInstrumentPreset('bass').tunings[0].strings.map(s => s.note + s.octave), ['E1', 'A1', 'D2', 'G2']);
});

test('rejects invalid frequency and note inputs', () => {
    assert.equal(freqToNote(0), null);
    assert.equal(noteToFrequency('H', 4), null);
    assert.equal(centsBetween(-1, 440), null);
});
