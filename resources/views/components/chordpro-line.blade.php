<div class="chordpro-line group py-1.5">
    @if ($chordLine !== '')
        <div class="chordpro-chord-row relative h-6 font-mono text-sm leading-none">
            @foreach ($chords as $chord)
                <button
                    type="button"
                    class="chord-trigger absolute top-0 cursor-pointer rounded px-0.5 font-semibold text-violet-400 transition hover:bg-violet-500/20 hover:text-violet-300 focus:outline-none focus:ring-1 focus:ring-violet-500/50"
                    style="left: {{ $chord['pos'] }}ch"
                    data-chord="{{ $chord['name'] }}"
                    aria-label="Ver diagrama de {{ $chord['name'] }}"
                >{{ $chord['name'] }}</button>
            @endforeach
        </div>
    @endif
    <div class="chordpro-lyric-row font-mono text-base leading-relaxed text-slate-100 sm:text-lg whitespace-pre">{{ $lyrics !== '' ? $lyrics : ' ' }}</div>
</div>
