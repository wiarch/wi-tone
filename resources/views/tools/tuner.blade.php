@push('head')
    @vite(['resources/js/musician-tools.js'])
@endpush

<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">🎯 Afinador cromático</h1>
        <p class="mt-1 text-sm text-slate-500">Micrófono en tiempo real o referencia por oído para cada cuerda</p>
    </div>

    <div data-tuner-page class="mx-auto max-w-lg overflow-hidden rounded-2xl border border-white/10 bg-[#0c1222] p-6">
        <div data-tuner class="space-y-4">
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
    </div>
</x-app-layout>
