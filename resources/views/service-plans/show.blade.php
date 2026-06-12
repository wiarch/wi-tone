<x-app-layout>
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $servicePlan->title }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $servicePlan->date->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('service-plans.index') }}" class="text-sm text-violet-400 hover:text-violet-300">← Volver a planes</a>
    </div>

    <div class="max-w-4xl space-y-6">
        @if (session('status') === 'plan-created')
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">Plan creado. Añade canciones al setlist.</div>
        @endif
        @if (session('status') === 'song-attached')
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">Canción añadida al plan.</div>
        @endif

        <x-admin-card title="Añadir al plan">
            <div class="p-6">
                @if ($availableSongs->isEmpty() && $search === '')
                    <p class="text-sm text-slate-500">
                        @if ($servicePlan->songs->isEmpty())
                            No hay canciones en la base de datos.
                            <a href="{{ route('songs.create') }}" class="text-violet-400 hover:underline">Registrar una</a>
                        @else
                            Todas las canciones ya están en este plan.
                        @endif
                    </p>
                @else
                    <form method="GET" action="{{ route('service-plans.show', $servicePlan) }}" class="mb-4 flex gap-2">
                        <input
                            type="search"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Buscar por título, artista o tono..."
                            class="block w-full admin-input"
                        />
                        <button type="submit" class="shrink-0 rounded-xl bg-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/15">Buscar</button>
                        @if ($search !== '')
                            <a href="{{ route('service-plans.show', $servicePlan) }}" class="shrink-0 rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-400 hover:text-slate-200">Limpiar</a>
                        @endif
                    </form>

                    @if ($availableSongs->isEmpty())
                        <p class="text-sm text-slate-500">Sin resultados para «{{ $search }}».</p>
                    @else
                        <form method="POST" action="{{ route('service-plans.songs.attach', $servicePlan) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="song_id" class="block text-sm font-medium text-slate-300">Canción</label>
                                <select id="song_id" name="song_id" required class="mt-1 block w-full admin-input" size="8">
                                    <option value="" disabled {{ old('song_id') ? '' : 'selected' }}>Seleccionar canción...</option>
                                    @foreach ($availableSongs as $song)
                                        <option value="{{ $song->id }}" @selected((string) old('song_id') === (string) $song->id)>
                                            {{ $song->title }} — {{ $song->artist }} ({{ $song->key }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('song_id')" />
                            </div>
                            <div class="flex justify-end">
                                <x-primary-button>Añadir al plan</x-primary-button>
                            </div>
                        </form>
                    @endif
                @endif
            </div>
        </x-admin-card>

        <x-admin-card title="Setlist">
            <x-slot:action>
                <span class="text-sm text-slate-500">{{ $servicePlan->songs->count() }} canción(es)</span>
            </x-slot:action>
            @if ($servicePlan->songs->isEmpty())
                <div class="p-10 text-center text-sm text-slate-500">Aún no hay canciones en este plan.</div>
            @else
                <ol class="divide-y divide-white/5">
                    @foreach ($servicePlan->songs as $song)
                        <li class="flex items-center gap-4 px-5 py-4 hover:bg-white/[0.02]">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-violet-600/20 text-sm font-semibold text-violet-300">{{ $song->pivot->order }}</span>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('songs.show', $song) }}" class="font-medium text-slate-200 hover:text-violet-300">{{ $song->title }}</a>
                                <p class="text-sm text-slate-500">{{ $song->artist }} · <span class="font-mono">{{ $song->key }}</span></p>
                            </div>
                            <a href="{{ route('songs.show', $song) }}" class="shrink-0 text-sm text-violet-400 hover:text-violet-300">Ver cifrado</a>
                        </li>
                    @endforeach
                </ol>
            @endif
        </x-admin-card>
    </div>

    <x-musician-tools-panel />
</x-app-layout>
