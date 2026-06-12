@push('head')
    @vite(['resources/js/song-reader-sidebar.js'])
@endpush

<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">🎹 Círculo de quintas</h1>
        <p class="mt-1 text-sm text-slate-500">Haz clic en una tonalidad para ver los acordes de su escala</p>
    </div>

    <div data-circle-page class="mx-auto max-w-lg overflow-hidden rounded-2xl border border-white/10 bg-[#0c1222] p-6">
        <div class="relative mx-auto aspect-square w-full max-w-[320px]">
            <svg data-sidebar-circle-svg viewBox="0 0 320 320" class="h-full w-full" role="img" aria-label="Círculo de quintas"></svg>
        </div>
        <p data-sidebar-circle-selected class="mt-4 text-center text-sm text-slate-400">Selecciona una tonalidad</p>
        <div data-sidebar-circle-chords class="mt-4 flex flex-wrap justify-center gap-2"></div>
    </div>
</x-app-layout>
