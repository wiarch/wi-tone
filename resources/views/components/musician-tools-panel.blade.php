@push('head')
    @vite(['resources/js/musician-tools.js'])
@endpush

<input type="checkbox" id="musician-tools-toggle" class="musician-tools-toggle peer hidden" aria-hidden="true">

<label
    for="musician-tools-toggle"
    class="fixed bottom-24 right-6 z-40 flex cursor-pointer items-center gap-2 rounded-full border border-white/10 bg-[#141c2e] px-4 py-2.5 text-sm font-medium text-slate-200 shadow-lg transition hover:border-violet-500/40 hover:bg-[#1a2438] peer-checked:border-violet-500/50 peer-checked:text-violet-200 lg:bottom-auto lg:right-0 lg:top-1/2 lg:-translate-y-1/2 lg:rounded-r-none lg:rounded-l-xl lg:py-4"
    aria-label="Herramientas del músico"
>
    <span class="text-base">🎵</span>
    <span class="hidden sm:inline">Herramientas</span>
</label>

<aside
    data-musician-tools
    class="musician-tools-panel fixed inset-y-0 right-0 z-50 flex w-full max-w-md translate-x-full flex-col border-l border-white/10 bg-[#0c1222] shadow-2xl transition-transform duration-300 peer-checked:translate-x-0 lg:max-w-sm"
    aria-label="Herramientas del músico"
>
    <div class="flex items-center justify-between border-b border-white/5 px-4 py-3">
        <h2 class="text-sm font-semibold text-white">Herramientas del músico</h2>
        <label for="musician-tools-toggle" class="cursor-pointer rounded-lg p-1.5 text-slate-400 hover:bg-white/5 hover:text-white" aria-label="Cerrar panel">✕</label>
    </div>

    <div class="flex-1 overflow-y-auto p-4 space-y-3">
        {{-- Afinador --}}
        <details class="musician-tool-section group rounded-xl border border-white/10 bg-[#141c2e]/60" open>
            <summary class="cursor-pointer list-none px-4 py-3 text-sm font-medium text-slate-200 [&::-webkit-details-marker]:hidden">
                <span class="flex items-center justify-between">
                    <span>🎸 Afinador cromático</span>
                    <span class="text-slate-500 transition group-open:rotate-180">▾</span>
                </span>
            </summary>
            <div data-tuner class="space-y-4 border-t border-white/5 px-4 py-4">
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-tuner-mic-start class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-500">Activar micrófono</button>
                    <button type="button" data-tuner-mic-stop class="hidden rounded-lg border border-white/10 px-3 py-1.5 text-xs text-slate-300 hover:bg-white/5">Detener</button>
                </div>
                <p data-tuner-status class="text-xs text-slate-500">Modo micrófono: detecta la frecuencia en tiempo real.</p>

                <div class="relative mx-auto max-w-[260px]">
                    <div class="relative h-28 overflow-hidden rounded-xl border border-white/10 bg-[#0a0f1a]">
                        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-emerald-500/10 to-transparent"></div>
                        <div class="absolute left-1/2 top-2 bottom-4 w-px -translate-x-1/2 bg-emerald-500/40"></div>
                        <div data-tuner-needle class="absolute bottom-2 left-1/2 h-[calc(100%-0.5rem)] w-0.5 origin-bottom -translate-x-1/2 rotate-0 rounded-full bg-violet-400 transition-transform duration-75"></div>
                    </div>
                    <div class="mt-1 flex justify-between text-[10px] text-slate-600">
                        <span>♭ bemol</span>
                        <span>afinado</span>
                        <span>sostenido ♯</span>
                    </div>
                </div>

                <div class="text-center">
                    <p data-tuner-note class="font-mono text-3xl font-bold text-white">—</p>
                    <p data-tuner-freq class="font-mono text-sm text-slate-500">0 Hz</p>
                    <p data-tuner-cents class="text-xs text-slate-400">—</p>
                </div>

                <div>
                    <p class="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500">Modo silencioso / por oído</p>
                    <div class="grid grid-cols-6 gap-1.5">
                        @foreach (['E', 'A', 'D', 'G', 'B', 'e'] as $string)
                            <button
                                type="button"
                                data-tuner-string="{{ $string }}"
                                class="rounded-lg border border-white/10 bg-white/5 py-2 font-mono text-xs text-violet-300 transition hover:border-violet-500/40 hover:bg-violet-600/20"
                            >{{ $string }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </details>

        {{-- Metrónomo --}}
        <details class="musician-tool-section group rounded-xl border border-white/10 bg-[#141c2e]/60">
            <summary class="cursor-pointer list-none px-4 py-3 text-sm font-medium text-slate-200 [&::-webkit-details-marker]:hidden">
                <span class="flex items-center justify-between">
                    <span>⏱ Metrónomo</span>
                    <span class="text-slate-500 transition group-open:rotate-180">▾</span>
                </span>
            </summary>
            <div data-metronome class="space-y-4 border-t border-white/5 px-4 py-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">BPM</span>
                    <span data-metronome-bpm-display class="font-mono text-lg font-semibold text-violet-300">120</span>
                </div>
                <input
                    type="range"
                    data-metronome-bpm
                    min="40"
                    max="250"
                    value="120"
                    class="w-full accent-violet-500"
                />

                <div>
                    <span class="mb-2 block text-xs text-slate-500">Compás</span>
                    <div class="flex gap-2">
                        @foreach (['4/4', '3/4', '6/8'] as $sig)
                            <button
                                type="button"
                                data-metronome-signature="{{ $sig }}"
                                class="flex-1 rounded-lg border px-2 py-1.5 text-xs font-medium transition {{ $sig === '4/4' ? 'border-violet-500/50 bg-violet-600/20 text-violet-200' : 'border-white/10 text-slate-400 hover:bg-white/5' }}"
                            >{{ $sig }}</button>
                        @endforeach
                    </div>
                </div>

                <div data-metronome-beats class="flex flex-wrap justify-center gap-2 min-h-[2rem]"></div>

                <button
                    type="button"
                    data-metronome-toggle
                    class="w-full rounded-xl bg-violet-600 py-2.5 text-sm font-medium text-white transition hover:bg-violet-500"
                >▶ Iniciar</button>
            </div>
        </details>

        {{-- Círculo de quintas --}}
        <details class="musician-tool-section group rounded-xl border border-white/10 bg-[#141c2e]/60">
            <summary class="cursor-pointer list-none px-4 py-3 text-sm font-medium text-slate-200 [&::-webkit-details-marker]:hidden">
                <span class="flex items-center justify-between">
                    <span>🎹 Círculo de quintas</span>
                    <span class="text-slate-500 transition group-open:rotate-180">▾</span>
                </span>
            </summary>
            <div data-circle-of-fifths class="space-y-4 border-t border-white/5 px-4 py-4">
                <div class="relative mx-auto aspect-square w-full max-w-[280px]">
                    <svg data-circle-svg viewBox="0 0 320 320" class="h-full w-full" role="img" aria-label="Círculo de quintas"></svg>
                </div>
                <div>
                    <p data-circle-selected class="text-center text-sm text-slate-400">Haz clic en una tonalidad</p>
                    <div data-circle-chords class="mt-3 flex flex-wrap justify-center gap-2"></div>
                </div>
            </div>
        </details>
    </div>
</aside>

<label
    for="musician-tools-toggle"
    class="musician-tools-backdrop fixed inset-0 z-40 hidden bg-black/50 peer-checked:block lg:hidden"
    aria-hidden="true"
></label>
