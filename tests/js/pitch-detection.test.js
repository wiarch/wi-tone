import test from 'node:test';
import assert from 'node:assert/strict';
import { detectPitch } from '../../resources/js/music/pitch-detection.js';

function sineBuffer(frequency, sampleRate = 48000, length = 8192) {
    return Float32Array.from({ length }, (_, index) => 0.5 * Math.sin((2 * Math.PI * frequency * index) / sampleRate));
}

test('detects standard guitar low E', () => {
    const result = detectPitch(sineBuffer(82.4069), 48000);
    assert.ok(result);
    assert.ok(Math.abs(result.frequency - 82.4069) < 1.5, 'detected ' + result.frequency + ' Hz');
    assert.ok(result.clarity > 0.8);
});

test('detects standard bass low E', () => {
    const result = detectPitch(sineBuffer(41.2034), 48000);
    assert.ok(result);
    assert.ok(Math.abs(result.frequency - 41.2034) < 1.5, 'detected ' + result.frequency + ' Hz');
});

test('rejects silence and invalid input', () => {
    assert.equal(detectPitch(new Float32Array(8192), 48000), null);
    assert.equal(detectPitch(null, 48000), null);
    assert.equal(detectPitch(sineBuffer(440), 0), null);
});
