@php
    $title = $instrument === 'guitar' ? 'Acordes — Guitarra' : 'Acordes — Teclado';
    $icon = $instrument === 'guitar' ? '🎸' : '🎹';
@endphp

@push('head')
    @vite(['resources/js/song-reader-sidebar.js'])
@endpush

<x-app-layout>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $icon }} {{ $title }}</h1>
            <p class="mt-1 text-sm text-slate-500">Diccionario global · selecciona un acorde para ver su diagrama</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('chords.guitar') }}" class="rounded-lg border px-3 py-2 text-sm font-medium transition {{ $instrument === 'guitar' ? 'border-violet-500/50 bg-violet-600/20 text-violet-200' : 'border-white/10 text-slate-400 hover:bg-white/5' }}">Guitarra</a>
            <a href="{{ route('chords.keyboard') }}" class="rounded-lg border px-3 py-2 text-sm font-medium transition {{ $instrument === 'keyboard' ? 'border-violet-500/50 bg-violet-600/20 text-violet-200' : 'border-white/10 text-slate-400 hover:bg-white/5' }}">Teclado</a>
        </div>
    </div>

    <div
        data-chord-dictionary-page
        data-instrument="{{ $instrument }}"
        data-diagram-library='@json($diagramLibrary)'
        data-diagrams-url="{{ route('chords.diagrams') }}"
        class="grid gap-6 lg:grid-cols-[minmax(0,260px)_1fr]"
    >
        <x-admin-card title="Diccionario">
            <div class="max-h-[calc(100vh-14rem)] overflow-y-auto p-4">
                @if ($chords->isEmpty())
                    <p class="text-sm text-slate-500">No hay acordes con diagramas de {{ $instrument === 'guitar' ? 'guitarra' : 'teclado' }}.</p>
                @else
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($chords as $chord)
                            <button
                                type="button"
                                data-chord-pick="{{ $chord->name }}"
                                class="rounded-md border border-white/10 bg-white/5 px-2.5 py-1.5 font-mono text-xs text-violet-300 transition hover:border-violet-500/40 hover:bg-violet-600/20"
                            >{{ $chord->name }}</button>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-admin-card>

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#0c1222] p-4 sm:p-6">
            <nav class="mb-4 flex gap-1 rounded-lg border border-white/10 bg-white/5 p-1" role="tablist">
                <button type="button" data-chord-viz-tab="guitar" class="chord-viz-tab flex-1 rounded-md px-2 py-1.5 text-xs font-medium {{ $instrument === 'guitar' ? 'bg-violet-600 text-white' : 'text-slate-400' }}">Guitarra</button>
                <button type="button" data-chord-viz-tab="keyboard" class="chord-viz-tab flex-1 rounded-md px-2 py-1.5 text-xs font-medium {{ $instrument === 'keyboard' ? 'bg-violet-600 text-white' : 'text-slate-400' }}">Teclado</button>
            </nav>

            <p data-chord-viz-title class="mb-4 text-center font-mono text-2xl font-bold text-white">—</p>
            <p data-chord-viz-hint class="mb-4 text-center text-xs text-slate-500">Selecciona un acorde de la lista</p>

            <div data-chord-viz-guitar class="{{ $instrument === 'keyboard' ? 'hidden' : '' }} overflow-x-auto">
                <svg data-guitar-svg viewBox="0 0 300 168" class="mx-auto h-auto w-full max-w-[360px]" aria-label="Mástil de guitarra"></svg>
            </div>
            <div data-chord-viz-keyboard class="{{ $instrument === 'guitar' ? 'hidden' : '' }} overflow-x-auto">
                <svg data-keyboard-svg viewBox="0 0 320 100" class="mx-auto h-auto w-full max-w-[360px]" aria-label="Teclado de piano"></svg>
            </div>
        </div>
    </div>
</x-app-layout>
