# WI Tone — Roadmap

This roadmap separates existing functionality from planned work. A roadmap item is not implemented until code and tests demonstrate it.

## Phase 0 — Product foundation

- [x] Define WI Tone as a digital instrument tuner.
- [x] Document current stack and existing audio implementation.
- [x] Identify legacy worship-planning modules as outside the primary product scope.
- [ ] Establish automated baseline checks for the current repository.
- [ ] Add product screenshots and a concise demo once the interface is ready.

## Phase 1 — Reliable core tuner

- [ ] Extract pitch and note calculations into independently testable modules.
- [ ] Add deterministic tests with generated reference frequencies.
- [ ] Improve fundamental-frequency estimation and confidence handling.
- [ ] Add smoothing/hysteresis to reduce unstable visual updates.
- [ ] Handle silence, noise, weak input, clipping and multiple simultaneous notes.
- [ ] Ensure complete microphone and AudioContext cleanup.
- [ ] Improve permission, unsupported-browser and device-error states.
- [ ] Validate tuning behavior on real guitar strings across a range of devices.

## Phase 2 — Instruments and presets

- [ ] Introduce a data-driven instrument/tuning preset model.
- [ ] Add standard six-string guitar tuning.
- [ ] Add standard four-string bass tuning.
- [ ] Add chromatic and string-by-string preset modes.
- [ ] Add alternative guitar tunings.
- [ ] Add alternative bass tunings.
- [ ] Make A4 reference pitch configurable.
- [ ] Add custom tuning profiles after preset behavior is stable.

## Phase 3 — Practice experience

- [ ] Redesign tuner UI around a clear, mobile-first instrument workflow.
- [ ] Improve the metronome and its accessibility.
- [ ] Add useful reference-tone controls.
- [ ] Improve theory/reference tools without making them the main navigation.
- [ ] Add local preferences for selected instrument and tuning.

## Phase 4 — Platform reach

- [ ] Evaluate PWA installation and offline application shell.
- [ ] Test microphone behavior across supported mobile browsers.
- [ ] Evaluate desktop packaging.
- [ ] Evaluate native mobile packaging only after validating the web experience.
- [ ] Document supported browsers and known hardware limitations.

## Phase 5 — Legacy module decision

- [ ] Audit song, chord, category, contact and service-plan modules.
- [ ] Map database and route dependencies.
- [ ] Decide whether each module should be removed, archived or moved to a separate repository.
- [ ] Prepare a migration and backup plan before deleting data or migrations.
- [ ] Execute the cleanup in a dedicated change set.

## Release criteria

A release focused on tuning should not be considered ready until:
- Pitch conversion and preset logic have automated tests.
- Microphone lifecycle cleanup is verified.
- Permission and unsupported-device states are usable.
- Guitar and bass presets have been validated with known frequencies and real instruments.
- Documentation accurately reflects the supported browser/device matrix.
