# WI Tone — Product Definition

## 1. Vision

WI Tone is a practical digital tuner that helps musicians tune instruments with confidence, from a browser on desktop or mobile. The experience should be immediate: choose an instrument or chromatic mode, grant microphone access, play a note and receive understandable tuning feedback.

The product takes inspiration from the simplicity of established tuner applications, while maintaining its own identity and implementation.

## 2. Product positioning

**Category:** digital instrument tuner and lightweight music-practice toolkit.

**Primary value:** accessible, real-time instrument tuning without requiring a dedicated hardware tuner or native installation.

**Primary platforms:** responsive web first. PWA, desktop and mobile packaging are future delivery targets.

## 3. Target users

- Guitarists who need quick standard or alternate tuning.
- Bassists who need reliable low-frequency detection.
- Musicians who want a chromatic tuner for instruments beyond guitar.
- Beginners who benefit from visual guidance and reference tones.
- Practicing musicians who also need a metronome and basic theory references.

## 4. Product pillars

### A. Accurate tuning
A reliable pitch-detection pipeline, stable note identification, cents deviation and clear feedback.

### B. Instrument-aware presets
A data-driven collection of instruments, string configurations and tunings. Guitar and bass are the first priorities.

### C. Accessible interaction
Fast startup, clear microphone permission handling, mobile-friendly controls and useful feedback for different accessibility needs.

### D. Privacy by design
Audio is analyzed locally in the browser by default. Microphone use is explicit and temporary.

### E. Useful practice tools
A metronome and selected theory/reference utilities complement the tuner without competing with it.

## 5. Scope

### Existing capabilities in the repository
- Chromatic pitch detection through Web Audio API using normalized autocorrelation.
- Frequency, note, octave and cents deviation display.
- Quality threshold to reject weak or unstable signal estimates.
- Instrument selector for guitar and bass.
- Standard and Drop D reference presets for guitar and bass.
- Manual reference tones for each string in the selected preset.
- Metronome with adjustable tempo and selected time signatures.
- Circle-of-fifths utility.
- Laravel authentication and account scaffolding.
- Legacy song, chord, contact and service-plan workflows.

### Core product scope to build
- Robust chromatic tuner.
- Instrument selector and data-driven tuning presets.
- Standard guitar and bass presets.
- Configurable reference pitch.
- Stable visual tuning states and confidence handling.
- Better low-frequency detection for bass.
- Responsive and accessible tuner experience.
- Automated tests for audio math and tuning data.

### Later scope
- Alternative tunings and custom tuning profiles.
- Additional instruments such as ukulele and violin.
- PWA installation and offline-capable application shell.
- Desktop and mobile wrappers if product validation justifies them.
- Expanded practice tools and optional local preferences.

### Out of scope for the primary product
- Worship service planning and scheduling.
- Team and contact management.
- A song catalog or repertoire-management platform.
- Music streaming or social-network features.
- Cloud recording or audio upload.

Legacy features are not to be deleted automatically. Any removal or extraction requires a dedicated migration plan.

## 6. Product principles

1. Tuning is the first and most prominent action.
2. The interface must explain what the musician should do next.
3. Do not show false precision or claim accuracy without validation.
4. A chromatic tuner and a preset string tuner are distinct modes.
5. Instrument configuration must be data-driven, not hardcoded to guitar.
6. Microphone access must be transparent, temporary and user-controlled.
7. Essential tuning must remain usable without creating an account.
8. Keep the interface focused; advanced settings should not overwhelm first-time users.

## 7. Success criteria

- A first-time user can start tuning without onboarding friction.
- Standard guitar strings are recognized consistently in a controlled test environment.
- Bass fundamentals can be detected across the supported frequency range.
- The interface distinguishes flat, in-tune and sharp states consistently.
- Microphone resources are released after stopping the tuner.
- Core pitch math and preset definitions have automated tests.
- The application works responsively on supported modern desktop and mobile browsers.

## 8. Product language

Use clear terms: instrument, tuning, string, note, frequency, cents, reference pitch, microphone, start and stop. Avoid claims such as “studio-grade accuracy” until measured and substantiated.
