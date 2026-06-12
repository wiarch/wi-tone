@push('head')
    @vite(['resources/js/musician-tools.js'])
@endpush

<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">⏱ Metrónomo</h1>
        <p class="mt-1 text-sm text-slate-500">Tempo ajustable con compases 4/4, 3/4 y 6/8</p>
    </div>

    <div data-metronome-page class="mx-auto max-w-lg overflow-hidden rounded-2xl border border-white/10 bg-[#0c1222] p-6">
        <div data-metronome class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-500">BPM</span>
                <span data-metronome-bpm-display class="font-mono text-2xl font-bold text-violet-300">120</span>
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
    </div>
</x-app-layout>
