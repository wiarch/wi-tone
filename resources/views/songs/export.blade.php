@php
    $title = $song->title.' — Exportar';
@endphp

<x-export-layout>
    @push('head')
        @vite(['resources/js/song-export.js'])
    @endpush

    <div
        data-song-export
        data-lines='@json($parsedLines)'
        data-song-key="{{ $song->key }}"
        data-diagram-library='@json($diagramLibrary)'
        data-chord-names='@json($songChordNames)'
        class="song-export min-h-screen bg-white text-gray-900"
        style="--export-chord-color: #e85d04;"
    >
        {{-- Panel flotante (oculto al imprimir) --}}
        <aside data-export-panel class="export-panel fixed right-4 top-4 z-50 w-64 rounded-xl border border-gray-200 bg-white p-4 shadow-xl print:hidden">
            <p class="mb-3 text-sm font-semibold text-gray-800">Opciones de exportación</p>

            <div class="mb-3 flex items-center justify-between">
                <span class="text-xs text-gray-500">Tamaño texto</span>
                <div class="flex gap-1">
                    <button type="button" data-font-down class="flex h-8 w-8 items-center justify-center rounded border border-gray-200 text-sm font-bold hover:bg-gray-50">A−</button>
                    <button type="button" data-font-up class="flex h-8 w-8 items-center justify-center rounded border border-gray-200 text-sm font-bold hover:bg-gray-50">A+</button>
                </div>
            </div>

            <div class="mb-3">
                <span class="mb-1.5 block text-xs text-gray-500">Color acordes</span>
                <div class="flex gap-2">
                    @foreach (['#e85d04', '#2563eb', '#7c3aed', '#059669', '#dc2626'] as $color)
                        <button type="button" data-accent-color="{{ $color }}" class="h-7 w-7 rounded-full border-2 border-white shadow ring-1 ring-gray-200" style="background: {{ $color }}"></button>
                    @endforeach
                </div>
            </div>

            <div class="mb-3">
                <span class="mb-1.5 block text-xs text-gray-500">Diagramas</span>
                <select data-export-instrument class="w-full rounded-lg border border-gray-200 px-2 py-1.5 text-sm">
                    <option value="guitar">Guitarra</option>
                    <option value="keyboard">Teclado</option>
                </select>
            </div>

            <div class="mb-4 space-y-2 text-sm text-gray-700">
                <label class="flex items-center gap-2"><input type="checkbox" data-toggle-chords checked class="rounded"> Acordes</label>
                <label class="flex items-center gap-2"><input type="checkbox" data-toggle-lyrics checked class="rounded"> Letra</label>
                <label class="flex items-center gap-2"><input type="checkbox" data-toggle-diagrams checked class="rounded"> Diagramas</label>
            </div>

            <button type="button" data-print-btn class="w-full rounded-lg bg-gray-900 py-2.5 text-sm font-medium text-white hover:bg-gray-800">Imprimir</button>
            <a href="{{ route('songs.show', $song) }}" class="mt-2 block text-center text-xs text-gray-500 hover:text-gray-700">← Volver al cifrado</a>
        </aside>

        {{-- Documento imprimible --}}
        <article data-export-document class="export-document mx-auto max-w-4xl px-6 py-10 print:max-w-none print:px-0 print:py-0">
            <header class="export-header mb-6 border-b border-gray-200 pb-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $song->title }}</h1>
                        <p class="export-artist mt-1 text-xl font-semibold" style="color: var(--export-chord-color)">{{ $song->artist }}</p>
                    </div>
                    <span class="text-xs font-semibold text-gray-400">{{ config('app.name') }}</span>
                </div>
                <p class="mt-3 text-sm text-gray-600">
                    Tono: <strong class="font-mono">{{ $song->key }}</strong>
                    <span class="mx-2 text-gray-300">|</span>
                    Afinación: <span class="font-mono">E A D G B E</span>
                </p>
            </header>

            <div data-export-sheet class="export-sheet columns-1 gap-8 font-mono text-[13px] leading-relaxed md:columns-2 print:columns-2"></div>

            <section data-export-diagrams-section class="export-diagrams mt-10 border-t border-gray-200 pt-8">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">Diagramas de acordes</h2>
                <div data-export-diagrams class="grid grid-cols-4 gap-4 sm:grid-cols-6 md:grid-cols-8"></div>
            </section>

            <footer class="export-footer mt-8 hidden text-right text-xs text-gray-400 print:block">
                Generado con {{ config('app.name') }}
            </footer>
        </article>
    </div>
</x-export-layout>
