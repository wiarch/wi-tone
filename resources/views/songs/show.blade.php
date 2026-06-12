@php
    $guitar = $song->chords->firstWhere('instrument', 'guitar');
    $keyboard = $song->chords->firstWhere('instrument', 'keyboard');
    $defaultTab = $guitar ? 'guitar' : ($keyboard ? 'keyboard' : 'guitar');
    $hasChords = $guitar || $keyboard;
@endphp

@push('head')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500&display=swap" rel="stylesheet">
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between song-show-header">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight song-show-title">
                    {{ $song->title }}
                </h2>
                <p class="text-sm text-gray-500 mt-1 song-show-meta">
                    {{ $song->artist }} · Tono: <span class="font-mono font-medium">{{ $song->key }}</span>
                </p>
            </div>
            <a href="{{ route('songs.edit', $song) }}" class="song-show-edit inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                Editar
            </a>
        </div>
    </x-slot>

    <div
        x-data="{
            tab: '{{ $defaultTab }}',
            stageMode: localStorage.getItem('wi-tone-stage') === 'true',
            setStageMode(value) {
                this.stageMode = value;
                localStorage.setItem('wi-tone-stage', value);
                document.body.classList.toggle('song-stage-mode', value);
            },
            toggleStage() {
                this.setStageMode(!this.stageMode);
            },
        }"
        x-init="
            setStageMode(stageMode);
            return () => document.body.classList.remove('song-stage-mode');
        "
    >
        <div class="py-6 sm:py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

                @if (session('status') === 'song-created')
                    <div x-show="!stageMode" class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                        Canción registrada correctamente.
                    </div>
                @endif
                @if (session('status') === 'song-updated')
                    <div x-show="!stageMode" class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                        Canción actualizada correctamente.
                    </div>
                @endif

                <div
                    class="overflow-hidden shadow-sm sm:rounded-xl transition-colors duration-300"
                    :class="stageMode
                        ? 'bg-slate-900 ring-1 ring-white/10 shadow-2xl shadow-black/40'
                        : 'bg-white ring-1 ring-gray-200'"
                >
                    <div
                        class="sticky top-0 z-10 flex flex-col gap-3 border-b px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6 backdrop-blur-md"
                        :class="stageMode
                            ? 'border-white/10 bg-slate-900/95'
                            : 'border-gray-200 bg-white/95'"
                    >
                        @if ($hasChords)
                            <nav class="flex gap-1 rounded-lg p-1" :class="stageMode ? 'bg-slate-800' : 'bg-gray-100'" role="tablist" aria-label="Instrumento">
                                @if ($guitar)
                                    <button
                                        type="button"
                                        role="tab"
                                        :aria-selected="tab === 'guitar'"
                                        @click="tab = 'guitar'"
                                        class="rounded-md px-4 py-2 text-sm font-medium transition"
                                        :class="tab === 'guitar'
                                            ? (stageMode ? 'bg-indigo-600 text-white shadow' : 'bg-white text-indigo-700 shadow-sm')
                                            : (stageMode ? 'text-slate-400 hover:text-slate-200' : 'text-gray-600 hover:text-gray-900')"
                                    >
                                        Guitarra
                                    </button>
                                @endif
                                @if ($keyboard)
                                    <button
                                        type="button"
                                        role="tab"
                                        :aria-selected="tab === 'keyboard'"
                                        @click="tab = 'keyboard'"
                                        class="rounded-md px-4 py-2 text-sm font-medium transition"
                                        :class="tab === 'keyboard'
                                            ? (stageMode ? 'bg-indigo-600 text-white shadow' : 'bg-white text-indigo-700 shadow-sm')
                                            : (stageMode ? 'text-slate-400 hover:text-slate-200' : 'text-gray-600 hover:text-gray-900')"
                                    >
                                        Teclado
                                    </button>
                                @endif
                            </nav>
                        @else
                            <p class="text-sm" :class="stageMode ? 'text-slate-500' : 'text-gray-500'">Sin cifrados</p>
                        @endif

                        <button
                            type="button"
                            @click="toggleStage()"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition"
                            :class="stageMode
                                ? 'border-amber-500/40 bg-amber-500/10 text-amber-300 hover:bg-amber-500/20'
                                : 'border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100'"
                        >
                            <svg x-show="!stageMode" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                            <svg x-show="stageMode" x-cloak class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                            <span>Modo altar</span>
                            <span x-show="stageMode" x-cloak class="text-amber-400/80">· activo</span>
                        </button>
                    </div>

                    <div
                        class="overflow-x-auto p-4 sm:p-8"
                        :class="stageMode ? 'bg-slate-950' : 'bg-gray-50/50'"
                    >
                        @if ($guitar)
                            <div x-show="tab === 'guitar'" x-cloak role="tabpanel" aria-label="Cifrado guitarra">
                                <pre class="chord-sheet font-mono text-base sm:text-lg leading-[1.75] whitespace-pre select-text" :class="stageMode ? 'text-slate-100' : 'text-gray-900'">{{ $guitar->content }}</pre>
                            </div>
                        @endif

                        @if ($keyboard)
                            <div x-show="tab === 'keyboard'" x-cloak role="tabpanel" aria-label="Cifrado teclado">
                                <pre class="chord-sheet font-mono text-base sm:text-lg leading-[1.75] whitespace-pre select-text" :class="stageMode ? 'text-slate-100' : 'text-gray-900'">{{ $keyboard->content }}</pre>
                            </div>
                        @endif

                        @if (! $hasChords)
                            <p class="text-center text-sm py-12" :class="stageMode ? 'text-slate-500' : 'text-gray-500'">
                                Esta canción no tiene cifrados registrados.
                                <a href="{{ route('songs.edit', $song) }}" class="text-indigo-500 hover:underline">Agregar cifrado</a>
                            </p>
                        @endif
                    </div>
                </div>

                <p x-show="stageMode" x-cloak class="text-center text-xs text-slate-500">
                    Lectura nocturna para el escenario. La preferencia se guarda en este navegador.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
