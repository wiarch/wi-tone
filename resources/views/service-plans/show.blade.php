<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $servicePlan->title }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $servicePlan->date->format('d/m/Y') }}
                </p>
            </div>
            <a href="{{ route('service-plans.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                Nuevo plan
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status') === 'plan-created')
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                    Plan creado. Añade canciones al setlist.
                </div>
            @endif
            @if (session('status') === 'song-attached')
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                    Canción añadida al plan.
                </div>
            @endif

            {{-- Añadir canción --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900">Añadir al plan</h3>
                    <p class="mt-1 text-sm text-gray-500">Busca y selecciona una canción del repertorio.</p>

                    @if ($availableSongs->isEmpty())
                        <p class="mt-4 text-sm text-gray-500">
                            @if ($servicePlan->songs->isEmpty())
                                No hay canciones en la base de datos.
                                <a href="{{ route('songs.create') }}" class="text-indigo-600 hover:underline">Registrar una canción</a>
                            @else
                                Todas las canciones disponibles ya están en este plan.
                            @endif
                        </p>
                    @else
                        <form
                            method="POST"
                            action="{{ route('service-plans.songs.attach', $servicePlan) }}"
                            class="mt-4 space-y-4"
                            x-data="{
                                query: '',
                                songId: '{{ old('song_id') }}',
                                songs: @js($availableSongs->map(fn ($s) => [
                                    'id' => $s->id,
                                    'label' => $s->title.' — '.$s->artist.' ('.$s->key.')',
                                    'search' => strtolower($s->title.' '.$s->artist.' '.$s->key),
                                ])),
                                get filtered() {
                                    if (!this.query.trim()) return this.songs;
                                    const q = this.query.toLowerCase();
                                    return this.songs.filter(s => s.search.includes(q));
                                },
                            }"
                        >
                            @csrf

                            <div>
                                <label for="song-search" class="block text-sm font-medium text-gray-700">Buscar canción</label>
                                <input
                                    id="song-search"
                                    type="search"
                                    x-model="query"
                                    placeholder="Título, artista o tono..."
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    autocomplete="off"
                                />
                            </div>

                            <div>
                                <label for="song_id" class="block text-sm font-medium text-gray-700">Canción</label>
                                <select
                                    id="song_id"
                                    name="song_id"
                                    x-model="songId"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    size="6"
                                >
                                    <option value="" disabled>Seleccionar canción...</option>
                                    <template x-for="song in filtered" :key="song.id">
                                        <option :value="song.id" x-text="song.label"></option>
                                    </template>
                                </select>
                                <p x-show="filtered.length === 0" class="mt-2 text-sm text-gray-500">Sin resultados para esa búsqueda.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('song_id')" />
                            </div>

                            <div class="flex justify-end">
                                <x-primary-button>Añadir al plan</x-primary-button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Setlist --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-100 sm:px-8">
                    <h3 class="text-lg font-semibold text-gray-900">Setlist</h3>
                    <p class="text-sm text-gray-500">{{ $servicePlan->songs->count() }} canción(es)</p>
                </div>

                @if ($servicePlan->songs->isEmpty())
                    <div class="p-8 text-center text-sm text-gray-500">
                        Aún no hay canciones en este plan.
                    </div>
                @else
                    <ol class="divide-y divide-gray-100">
                        @foreach ($servicePlan->songs as $song)
                            <li class="flex items-center gap-4 px-6 py-4 sm:px-8 hover:bg-gray-50/80">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">
                                    {{ $song->pivot->order }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('songs.show', $song) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $song->title }}
                                    </a>
                                    <p class="text-sm text-gray-500">
                                        {{ $song->artist }} · Tono: <span class="font-mono">{{ $song->key }}</span>
                                    </p>
                                </div>
                                <a href="{{ route('songs.show', $song) }}" class="shrink-0 text-sm text-indigo-600 hover:text-indigo-800">
                                    Ver cifrado
                                </a>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
