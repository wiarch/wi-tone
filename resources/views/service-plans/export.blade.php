@php
    $title = $servicePlan->title.' — '.($pageMode === 'share' ? 'Compartir' : 'Exportar');
    $lyricColors = ['#111827', '#e85d04', '#db2777', '#2563eb', '#059669'];
    $chordColors = ['#2563eb', '#e85d04', '#7c3aed', '#059669', '#dc2626'];
    $planMeta = [
        'title' => $servicePlan->title,
        'date' => $servicePlan->date->toDateString(),
        'notes' => $servicePlan->notes,
    ];
@endphp

<x-export-layout>
    @push('head')
        @vite(['resources/js/service-plan-export.js'])
    @endpush

    <div
        data-plan-export
        data-page-mode="{{ $pageMode }}"
        data-diagram-library='@json($diagramLibrary)'
        class="plan-export-page min-h-screen bg-gray-50 text-gray-900"
        style="--plan-lyric-color: #111827; --plan-chord-color: #2563eb; --plan-title-color: #111827;"
    >
        <script type="application/json" data-plan-entries>@json($entries)</script>
        <script type="application/json" data-plan-meta>@json($planMeta)</script>
        <script type="application/json" data-share-settings>@json($shareSettings ?? [])</script>

        @if ($pageMode !== 'public')
        <aside data-export-panel class="export-panel fixed right-4 top-4 z-50 w-72 rounded-2xl border border-gray-200 bg-white p-4 shadow-xl print:hidden">
            <p class="mb-3 text-sm font-semibold text-gray-800">{{ $pageMode === 'share' ? 'Vista compartir' : 'Opciones de impresión' }}</p>

            <div class="mb-3">
                <label class="mb-1 block text-xs text-gray-500">Tamaño papel</label>
                <select data-paper-size class="w-full rounded-lg border border-gray-200 px-2 py-1.5 text-sm">
                    <option value="a4">A4</option>
                    <option value="letter">Carta</option>
                </select>
            </div>

            <div class="mb-3">
                <span class="mb-1.5 block text-xs text-gray-500">Letras y título</span>
                <div class="flex gap-2">
                    @foreach ($lyricColors as $color)
                        <button type="button" data-lyric-color="{{ $color }}" class="h-7 w-7 rounded-full border-2 border-white shadow ring-1 ring-gray-200" style="background:{{ $color }}"></button>
                    @endforeach
                </div>
            </div>

            <div class="mb-3">
                <span class="mb-1.5 block text-xs text-gray-500">Acordes y artista</span>
                <div class="flex gap-2">
                    @foreach ($chordColors as $color)
                        <button type="button" data-chord-color="{{ $color }}" class="h-7 w-7 rounded-full border-2 border-white shadow ring-1 ring-gray-200" style="background:{{ $color }}"></button>
                    @endforeach
                </div>
            </div>

            <div class="mb-3 flex items-center justify-between">
                <span class="text-xs text-gray-500">Tamaño texto</span>
                <div class="flex items-center gap-2">
                    <button type="button" data-font-down class="flex h-8 w-8 items-center justify-center rounded border border-gray-200 text-sm font-bold hover:bg-gray-50">A−</button>
                    <span data-font-size-label class="min-w-[3rem] text-center text-xs font-mono text-gray-600">14px</span>
                    <button type="button" data-font-up class="flex h-8 w-8 items-center justify-center rounded border border-gray-200 text-sm font-bold hover:bg-gray-50">A+</button>
                </div>
            </div>

            <div class="mb-3 flex items-center justify-between">
                <span class="text-xs text-gray-500">Tonalidad</span>
                <div class="flex items-center gap-2">
                    <button type="button" data-transpose-down class="flex h-8 w-8 items-center justify-center rounded border border-gray-200 hover:bg-gray-50">−</button>
                    <span data-transpose-label class="min-w-[2rem] text-center font-mono text-xs text-gray-700">0</span>
                    <button type="button" data-transpose-up class="flex h-8 w-8 items-center justify-center rounded border border-gray-200 hover:bg-gray-50">+</button>
                </div>
            </div>

            <div class="mb-4 space-y-2 text-sm text-gray-700">
                <label class="flex items-center gap-2"><input type="checkbox" data-toggle-chords checked class="rounded"> Acordes</label>
                <label class="flex items-center gap-2"><input type="checkbox" data-toggle-lyrics checked class="rounded"> Letra</label>
                <label class="flex items-center gap-2"><input type="checkbox" data-toggle-index checked class="rounded"> Índice del plan</label>
                <label class="flex items-center gap-2"><input type="checkbox" data-toggle-diagrams class="rounded"> Diagramas</label>
            </div>

            <button type="button" data-print-btn class="w-full rounded-lg bg-gray-900 py-2.5 text-sm font-medium text-white hover:bg-gray-800">Imprimir / PDF</button>
            <button type="button" data-reset-btn class="mt-2 w-full text-center text-xs text-gray-500 hover:text-gray-700">Restablecer ajustes</button>

            @if ($pageMode === 'share')
                <div class="mt-4 border-t border-gray-100 pt-4">
                    @if (session('status') === 'plan-published')
                        <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-800">Plan publicado. Copia el enlace:</div>
                    @endif
                    @if (session('status') === 'plan-unpublished')
                        <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">Enlace desactivado.</div>
                    @endif

                    @if ($servicePlan->isPublished())
                        <label class="mb-1 block text-xs font-medium text-gray-600">Enlace público</label>
                        <div class="flex gap-1">
                            <input type="text" readonly value="{{ $servicePlan->publicUrl() }}" data-public-url class="min-w-0 flex-1 rounded-lg border border-gray-200 px-2 py-1.5 text-xs text-gray-700" />
                            <button type="button" data-copy-url class="shrink-0 rounded-lg border border-gray-200 px-2 text-xs hover:bg-gray-50">Copiar</button>
                        </div>
                        <form method="POST" action="{{ route('service-plans.unpublish', $servicePlan) }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Despublicar</button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('service-plans.publish', $servicePlan) }}" data-publish-form class="mt-3">
                        @csrf
                        <input type="hidden" name="share_settings" data-share-settings-input />
                        <button type="submit" class="w-full rounded-lg bg-amber-600 py-2.5 text-sm font-semibold text-white hover:bg-amber-500">
                            {{ $servicePlan->isPublished() ? 'Actualizar publicación' : 'Publicar' }}
                        </button>
                    </form>
                    <p class="mt-2 text-center text-[10px] text-gray-400">Cualquiera con el enlace verá el cifrado sin poder editarlo.</p>
                </div>
            @endif

            <div class="mt-3 flex gap-2 border-t border-gray-100 pt-3 text-xs">
                <a href="{{ route('service-plans.show', $servicePlan) }}" class="text-gray-500 hover:text-gray-800">← Plan</a>
                @if ($pageMode === 'share')
                    <a href="{{ route('service-plans.export', $servicePlan) }}" class="text-amber-600 hover:text-amber-700">Vista impresión</a>
                @else
                    <a href="{{ route('service-plans.share', $servicePlan) }}" class="text-amber-600 hover:text-amber-700">Vista compartir</a>
                @endif
            </div>
        </aside>
        @endif

        <article data-export-document @class([
            'export-document mx-auto max-w-4xl px-6 py-10 print:max-w-none print:px-0 print:py-0',
            'pr-[20rem]' => $pageMode !== 'public',
        ])>
            <header class="mb-8 border-b border-gray-200 pb-6">
                <p class="text-xs font-semibold uppercase tracking-widest plan-export-chord">Plan de dirección</p>
                <h1 class="mt-1 text-3xl font-bold plan-export-title">{{ $servicePlan->title }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ $servicePlan->date->translatedFormat('l, d \d\e F \d\e Y') }}</p>
                @if ($servicePlan->notes)
                    <p class="mt-3 text-sm plan-export-lyric">{{ $servicePlan->notes }}</p>
                @endif

                @if ($servicePlan->teamMembers->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($servicePlan->teamMembers as $member)
                            <span class="rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-sm">
                                <strong>{{ $member->name }}</strong>
                                <span class="text-gray-500">· {{ $member->voice_tone }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </header>

            <section data-plan-index class="mb-10"></section>

            <section>
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">
                    {{ in_array($pageMode, ['share', 'public'], true) ? 'Canciones del servicio' : 'Cifrado del servicio' }}
                </h2>
                <div data-plan-songs></div>
            </section>

            <section data-plan-diagrams-section class="mt-10 hidden border-t border-gray-200 pt-8">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">Diagramas</h2>
                <div data-plan-diagrams class="grid grid-cols-4 gap-4 sm:grid-cols-6"></div>
            </section>

            <footer class="mt-10 border-t border-gray-200 pt-4 text-center text-xs text-gray-400 print:block">
                Generado con {{ config('app.name') }} · {{ now()->format('d/m/Y H:i') }}
            </footer>
        </article>
    </div>
</x-export-layout>
