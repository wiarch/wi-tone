# WI Tone — Architecture

## 1. Current architecture

WI Tone is a Laravel application with server-rendered Blade views and browser-side JavaScript.

- **Backend:** PHP 8.3+ and Laravel 13.
- **Presentation:** Blade templates and Tailwind CSS.
- **Frontend build:** Vite.
- **Audio:** browser Web Audio API and MediaDevices API.
- **Current tuner entry point:** `resources/views/tools/tuner.blade.php`.
- **Current audio/tool UI logic:** `resources/js/musician-tools.js`.
- **Pitch and preset engine:** `resources/js/music/pitch-detection.js`.
- **Pitch engine tests:** `tests/js/pitch-detection.test.js`.
- **Current routes:** `routes/web.php`, including `tools.tuner` and `tools.metronome`.

The tuner obtains a microphone MediaStream, connects it to an AnalyserNode and analyzes a downsampled time-domain buffer. The pitch module applies normalized autocorrelation, rejects low-energy or low-confidence signals, refines the detected period and returns frequency, confidence and RMS. The note conversion module maps frequency to note, octave and cents deviation. Reference tones are generated with an OscillatorNode. Guitar and bass presets currently provide standard and Drop D reference frequencies.

## 2. Proposed logical boundaries

As the tuner grows, split the current combined JavaScript module into focused units:

- `audio/audio-input`: microphone permission, stream lifecycle and analyser setup.
- `audio/pitch-detection`: frequency estimation and confidence/quality metrics.
- `music/note-theory`: frequency-to-note conversion, MIDI note and cents math.
- `tuning/instrument-presets`: instrument and tuning definitions.
- `tuning/tuning-engine`: comparison between measured pitch and selected target.
- `audio/reference-tone`: oscillator-based reference tone playback.
- `ui/tuner-controller`: DOM bindings and accessible UI state.
- `metronome`: metronome scheduling and UI.
- `theory`: circle-of-fifths and related reference tools.

The pitch and preset module is already implemented at `resources/js/music/pitch-detection.js`. The remaining boundaries are proposed future extractions. Introduce them incrementally and keep the current routes and views working during the transition.

## 3. Audio processing flow

1. User activates the tuner.
2. Browser requests microphone permission.
3. The application creates an AudioContext and MediaStreamAudioSourceNode.
4. AnalyserNode supplies time-domain samples.
5. The pitch detector estimates the fundamental frequency and a confidence/quality value.
6. The note engine maps frequency to the nearest equal-tempered note using the configured A4 reference.
7. The tuning engine compares the measured pitch with the selected target.
8. UI displays note, frequency, cents and a stable tuning state.
9. User stops the tuner; tracks, animation frames and audio resources are released.

The current implementation includes a confidence threshold and low-frequency support for bass presets. More advanced harmonic rejection, temporal stability filtering, configurable reference pitch and real-device validation remain planned improvements.

## 4. Tuning data model

Instrument presets should be represented as data, not UI-specific conditionals. A preset should include:
- Stable identifier.
- Instrument identifier.
- Display name and description.
- Ordered string definitions.
- Note name and octave for each string.
- Target frequency derived from the reference pitch or explicitly validated.
- Optional metadata such as supported range or tuning family.

Do not assume all instruments have six strings. Keep chromatic mode separate from a selected-string preset mode.

## 5. Browser compatibility

Microphone access requires a secure context in supported browsers (HTTPS, with localhost generally permitted for local development). Feature-detect:
- `navigator.mediaDevices?.getUserMedia`
- `window.AudioContext` or supported prefixed fallback where needed
- `AnalyserNode.getFloatTimeDomainData`

Handle denied permission, missing input devices, unsupported APIs, suspended contexts and device disconnection without crashing.

## 6. Resource lifecycle

The tuner must own and release its resources:
- Stop each MediaStreamTrack.
- Cancel requestAnimationFrame loops.
- Disconnect audio nodes where appropriate.
- Close AudioContext instances when no longer needed.
- Clear timers and event listeners on disposal.
- Avoid opening duplicate microphone streams after repeated start/stop actions.

## 7. Legacy application modules

The current Laravel application also contains song, chord, category, contact and service-plan modules. These are legacy product scope. They should remain isolated while the tuner direction is implemented. A future cleanup must trace routes, controllers, models, migrations, views, navigation, authorization and tests before extraction or removal.

## 8. Testing strategy

- Node test-runner tests for frequency-to-note conversion and cents calculation.
- Tests for standard and Drop D guitar/bass preset shape and target frequencies.
- Deterministic pitch detector tests using generated sample buffers at known guitar and bass frequencies.
- Edge cases: silence, noise, weak signals, harmonics, low-frequency bass notes and out-of-range inputs.
- Browser tests for permission denial, start/stop lifecycle and responsive UI.
- Laravel feature tests for routes and any server-side preferences added later.
