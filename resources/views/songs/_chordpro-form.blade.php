@php
    $song = $song ?? null;
    $chordProContent = old(
        'content',
        $song?->chords?->firstWhere('instrument', 'guitar')?->content
            ?? $song?->chords?->firstWhere('instrument', 'keyboard')?->content
            ?? ''
    );
@endphp

<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="sm:col-span-1">
            <x-input-label for="title" value="Título" class="text-slate-300" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full admin-input" :value="old('title', $song?->title ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('title')" />
        </div>
        <div class="sm:col-span-1">
            <x-input-label for="artist" value="Artista / Autor" class="text-slate-300" />
            <x-text-input id="artist" name="artist" type="text" class="mt-1 block w-full admin-input" :value="old('artist', $song?->artist ?? '')" required />
            <x-input-error class="mt-2" :messages="$errors->get('artist')" />
        </div>
        <div class="sm:col-span-1">
            <x-input-label for="key" value="Tono principal" class="text-slate-300" />
            <x-text-input id="key" name="key" type="text" class="mt-1 block w-full admin-input" :value="old('key', $song?->key ?? '')" placeholder="Ej: G, D, Bm" required />
            <x-input-error class="mt-2" :messages="$errors->get('key')" />
        </div>
    </div>

    <div
        data-visual-chord-editor
        data-search-url="{{ route('chords.search') }}"
        class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#0c1222]"
    >
        <textarea data-initial-content class="hidden" readonly>{{ $chordProContent }}</textarea>
        <input type="hidden" name="content" data-content-field value="{{ $chordProContent }}">

        <div class="border-b border-white/5 px-4 py-3 sm:px-5">
            <h3 class="text-sm font-semibold text-white">Editor visual de cifrado</h3>
            <p class="mt-0.5 text-xs text-slate-500">
                Pega cifrado alineado (acordes arriba, letra abajo) o edita visualmente con drag &amp; drop.
            </p>
        </div>

        {{-- Paleta de acordes --}}
        <div class="border-b border-white/5 bg-[#141c2e]/40 px-4 py-3 sm:px-5">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Acordes de la canción</span>
                <button
                    type="button"
                    data-add-chord
                    class="inline-flex items-center gap-1.5 rounded-lg border border-violet-500/40 bg-violet-600/20 px-3 py-1.5 text-xs font-medium text-violet-200 transition hover:bg-violet-600/35"
                >
                    <span class="text-base leading-none">+</span> Añadir acorde
                </button>
            </div>
            <div data-chord-palette class="flex min-h-[36px] flex-wrap gap-2"></div>
        </div>

        {{-- Entrada de letra --}}
        <div class="border-b border-white/5 p-4 sm:p-5">
            <label for="lyrics-source" class="mb-2 block text-xs font-medium uppercase tracking-wider text-slate-500">Letra</label>
            <textarea
                id="lyrics-source"
                data-lyrics-source
                rows="8"
                class="block w-full resize-y rounded-xl border border-white/10 bg-[#0a0f1a] p-4 font-mono text-sm leading-relaxed text-slate-100 placeholder-slate-600 focus:border-violet-500/50 focus:outline-none focus:ring-1 focus:ring-violet-500/40"
                placeholder="Pega cifrado estilo UG: línea de acordes y debajo la letra. Ej:&#10; D    Bm7&#10;Tu fidelidad és grande"
            ></textarea>
            <p data-import-status class="mt-2 min-h-[1rem] text-xs"></p>
            <x-input-error class="mt-2" :messages="$errors->get('content')" />
        </div>

        {{-- Lienzo visual --}}
        <div class="p-4 sm:p-5">
            <div class="mb-3 flex items-center justify-between">
                <h4 class="text-xs font-medium uppercase tracking-wider text-slate-500">Cifrado visual</h4>
                <span class="text-xs text-slate-600">Arrastra un acorde sobre la palabra</span>
            </div>
            <div
                data-visual-canvas
                class="min-h-[200px] rounded-xl border border-dashed border-white/10 bg-[#0a0f1a] p-4 sm:p-5"
            ></div>
        </div>

        {{-- Buscador flotante para añadir acordes --}}
        <div
            data-chord-floater
            class="pointer-events-none fixed inset-x-4 top-1/3 z-50 mx-auto hidden max-w-sm opacity-0 transition-opacity duration-150 sm:inset-x-auto sm:left-1/2 sm:-translate-x-1/2"
            role="dialog"
            aria-label="Buscar acorde"
        >
            <div class="pointer-events-auto overflow-hidden rounded-xl border border-white/15 bg-[#141c2e] shadow-2xl shadow-black/50 ring-1 ring-violet-500/20">
                <div class="border-b border-white/5 px-3 py-2">
                    <input
                        type="search"
                        data-chord-search
                        placeholder="Buscar G, Em, C7…"
                        class="admin-input w-full text-sm"
                        autocomplete="off"
                    />
                    <p data-chord-status class="mt-1.5 text-xs text-slate-500">Escribe para buscar</p>
                </div>
                <div data-chord-results class="max-h-52 overflow-y-auto p-1.5"></div>
            </div>
        </div>
    </div>
</div>

@push('head')
    <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500&display=swap" rel="stylesheet">
    @vite(['resources/js/chord-visual-editor.js'])
@endpush
