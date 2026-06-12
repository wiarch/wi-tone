@props([
    'diagramLibrary' => [],
    'chordNames' => [],
])

@push('head')
    @vite(['resources/js/song-reader-sidebar.js'])
@endpush

<aside
    data-song-reader-sidebar
    data-diagram-library='@json($diagramLibrary)'
    data-diagrams-url="{{ route('chords.diagrams') }}"
    class="flex w-full shrink-0 flex-col overflow-hidden rounded-2xl border border-white/10 bg-[#0c1222] xl:w-80 xl:sticky xl:top-20 xl:max-h-[calc(100vh-6rem)]"
    aria-label="Panel lateral de lectura"
>
    <nav class="flex border-b border-white/5" role="tablist" aria-label="Herramientas">
        <button type="button" data-sidebar-tab="chords" class="sidebar-tab flex-1 border-b-2 border-violet-500 px-2 py-3 text-xs font-medium text-violet-200 sm:text-sm" aria-selected="true">Acordes</button>
        <button type="button" data-sidebar-tab="metronome" class="sidebar-tab flex-1 border-b-2 border-transparent px-2 py-3 text-xs font-medium text-slate-400 hover:text-slate-200 sm:text-sm" aria-selected="false">Metrónomo</button>
        <button type="button" data-sidebar-tab="circle" class="sidebar-tab flex-1 border-b-2 border-transparent px-2 py-3 text-xs font-medium text-slate-400 hover:text-slate-200 sm:text-sm" aria-selected="false">Quintas</button>
    </nav>

    <div class="flex-1 overflow-y-auto">
        {{-- Visualizador de acordes --}}
        <div data-sidebar-panel="chords" class="p-4">
            <nav class="mb-4 flex gap-1 rounded-lg border border-white/10 bg-white/5 p-1" role="tablist" aria-label="Instrumento">
                <button type="button" data-chord-viz-tab="guitar" class="chord-viz-tab flex-1 rounded-md bg-violet-600 px-2 py-1.5 text-xs font-medium text-white">Guitarra</button>
                <button type="button" data-chord-viz-tab="keyboard" class="chord-viz-tab flex-1 rounded-md px-2 py-1.5 text-xs font-medium text-slate-400 hover:text-slate-200">Teclado</button>
            </nav>

            <p data-chord-viz-title class="mb-3 text-center font-mono text-lg font-semibold text-white">—</p>

            <div data-chord-viz-guitar class="overflow-x-auto">
                <svg data-guitar-svg viewBox="0 0 300 168" class="mx-auto h-auto w-full max-w-[300px]" role="img" aria-label="Mástil de guitarra"></svg>
            </div>
            <div data-chord-viz-keyboard class="hidden overflow-x-auto">
                <svg data-keyboard-svg viewBox="0 0 320 100" class="mx-auto h-auto w-full max-w-[320px]" role="img" aria-label="Teclado de piano"></svg>
            </div>

            <p data-chord-viz-hint class="mt-3 text-center text-xs text-slate-500">Haz clic en un acorde de la letra</p>

            @if (count($chordNames))
                <div class="mt-4 border-t border-white/5 pt-4">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500">En esta canción</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($chordNames as $name)
                            <button
                                type="button"
                                data-chord-pick="{{ $name }}"
                                class="rounded-md border border-white/10 bg-white/5 px-2 py-1 font-mono text-xs text-violet-300 transition hover:border-violet-500/40 hover:bg-violet-600/20"
                            >{{ $name }}</button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Metrónomo --}}
        <div data-sidebar-panel="metronome" class="hidden p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-500">BPM</span>
                <span data-sidebar-bpm-display class="font-mono text-2xl font-bold text-violet-300">120</span>
            </div>
            <input type="range" data-sidebar-bpm min="40" max="250" value="120" class="mt-2 w-full accent-violet-500">
            <div data-sidebar-beats class="mt-4 flex justify-center gap-2"></div>
            <button type="button" data-sidebar-metro-toggle class="mt-4 w-full rounded-xl bg-violet-600 py-2.5 text-sm font-medium text-white hover:bg-violet-500">▶ Iniciar</button>
        </div>

        {{-- Círculo de quintas --}}
        <div data-sidebar-panel="circle" class="hidden p-4">
            <div class="relative mx-auto aspect-square w-full max-w-[260px]">
                <svg data-sidebar-circle-svg viewBox="0 0 320 320" class="h-full w-full" role="img" aria-label="Círculo de quintas"></svg>
            </div>
            <p data-sidebar-circle-selected class="mt-3 text-center text-sm text-slate-400">Selecciona una tonalidad</p>
            <div data-sidebar-circle-chords class="mt-3 flex flex-wrap justify-center gap-2"></div>
        </div>
    </div>
</aside>
